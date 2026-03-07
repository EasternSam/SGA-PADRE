<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\Payment;
use App\Services\EcfService;
use App\Services\MatriculaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CardnetController extends Controller
{
    /**
     * Procesa el callback de respuesta de Cardnet (pago exitoso o fallido).
     */
    public function handleResponse(Request $request, EcfService $ecfService, MatriculaService $matriculaService)
    {
        Log::info('Cardnet Debug: Retorno recibido en /cardnet/response', $request->all());

        $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
        $responseCode = $request->input('ResponseCode');
        $authCode = $request->input('AuthorizationCode');
        $txId = $request->input('TransactionId');
        $responseMessage = $request->input('ResponseMessage') ?? $request->input('ResponseMsg') ?? 'Transacción declinada sin mensaje específico';

        $payment = Payment::find($orderId);

        if (!$payment) {
            Log::error('Cardnet Error: Pago no encontrado ID: ' . $orderId);
            return redirect('/')->with('error', 'Error crítico: Pago no encontrado o sesión expirada.');
        }

        if (!Auth::check() && $payment->user_id) {
            Log::warning("Cardnet Debug: Sesión perdida detectada. Restaurando usuario ID {$payment->user_id}...");
            Auth::loginUsingId($payment->user_id);

            if (Auth::check()) {
                Log::info("Cardnet Debug: Sesión restaurada con éxito.");
            } else {
                Log::error("Cardnet Debug: Fallo al restaurar sesión.");
            }
        }

        if ($responseCode === '00') {
            return $this->handleSuccessfulPayment($payment, $authCode, $txId, $ecfService, $matriculaService);
        }

        return $this->handleFailedPayment($payment, $orderId, $responseCode, $responseMessage);
    }

    /**
     * Procesa la cancelación de un pago desde el flujo normal.
     */
    public function handleCancel(Request $request)
    {
        Log::info('Cardnet Debug: Cancelación detectada', $request->all());

        $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');

        if ($orderId) {
            $payment = Payment::find($orderId);

            if (!Auth::check() && $payment && $payment->user_id) {
                Auth::loginUsingId($payment->user_id);
            }

            if ($payment && $payment->status === 'Pendiente') {
                $payment->update(['status' => 'Pendiente', 'notes' => 'Cancelado por usuario']);
            }
        }

        if (Auth::check()) {
            return redirect()->route('student.payments')->with('error', 'Operación cancelada.');
        }

        return redirect('/')->with('error', 'Operación cancelada.');
    }

    /**
     * Procesa la cancelación de un pago desde el Kiosco.
     */
    public function handleKioskCancel(Request $request)
    {
        Log::info('Kiosk Cardnet Debug: Cancelación detectada', $request->all());

        $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');

        if ($orderId) {
            $payment = Payment::find($orderId);

            if ($payment && $payment->status === 'Pendiente') {
                $payment->update(['status' => 'Pendiente', 'notes' => 'Cancelado por usuario en Kiosco']);
            }
        }

        return redirect()->route('kiosk.finances')->with('notify', ['message' => 'Operación cancelada.', 'type' => 'warning']);
    }

    /**
     * Lógica interna para un pago exitoso.
     */
    private function handleSuccessfulPayment(Payment $payment, string $authCode, ?string $txId, EcfService $ecfService, MatriculaService $matriculaService)
    {
        $payment->update([
            'status' => 'Completado',
            'transaction_id' => $authCode,
            'notes' => "Aprobado Cardnet | Ref: {$txId} | Auth: {$authCode}",
        ]);

        try {
            $ecfService->emitirComprobante($payment);

            if ($payment->enrollment) {
                $payment->enrollment->status = 'Cursando';
                $payment->enrollment->save();
            }

            $student = $payment->student;

            if ($student && !$student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                $matriculaService->generarMatricula($payment);
            }

            if ($student && $student->email) {
                try {
                    $payment->load('student', 'paymentConcept', 'enrollment.courseSchedule.module');
                    $pdfOutput = Pdf::loadView('reports.thermal-invoice', ['payment' => $payment])->output();
                    $pdfBase64 = base64_encode($pdfOutput);
                    Mail::to($student->email)->send(new PaymentReceiptMail($payment, $pdfBase64));
                    Log::info("Cardnet: Correo de recibo enviado a {$student->email}");
                } catch (\Exception $e) {
                    Log::error("Cardnet Error enviando correo: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Cardnet Error post-proceso: " . $e->getMessage());
        }

        $user = Auth::user();

        if ($user && $user->hasRole('Estudiante')) {
            return redirect()->route('student.payments')->with('message', '¡Pago realizado con éxito! Código: ' . $authCode);
        }

        return redirect('/dashboard')->with('message', 'Pago procesado correctamente.');
    }

    /**
     * Lógica interna para un pago fallido/rechazado.
     */
    private function handleFailedPayment(Payment $payment, $orderId, string $responseCode, string $responseMessage)
    {
        $payment->update([
            'status' => 'Pendiente',
            'notes' => "Intento fallido Cardnet [{$responseCode}]: {$responseMessage}",
        ]);

        Log::warning("Cardnet Rechazo: Orden {$orderId} - Código {$responseCode} - Msg: {$responseMessage}");

        return redirect()->route('student.payments')->with('error', "El pago fue rechazado por el banco. Razón: {$responseMessage}");
    }
}
