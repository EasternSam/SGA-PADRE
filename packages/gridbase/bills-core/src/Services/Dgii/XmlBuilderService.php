<?php

namespace Gridbase\BillsCore\Services\Dgii;

use DOMDocument;
use Exception;

/**
 * Gridbase Bills Core — XmlBuilderService
 * Builds raw unsigned XML for Comprobantes Fiscales Electrónicos (e-CF types 31–47) and RFCE (FC < 250k)
 * according to the official DGII XSD specifications.
 */
class XmlBuilderService
{
    /**
     * Builds the e-CF XML from a normalized document array and emitter settings array.
     *
     * @param array $document Normalized invoice/payment data:
     *   - ecf_type (int|string: 31, 32, 33, 34, 41, 43, 44, 45, 46, 47)
     *   - encf (string: e.g. E320000000001)
     *   - issue_date (string|\DateTimeInterface: Y-m-d)
     *   - due_date (string|\DateTimeInterface|null)
     *   - subtotal (float)
     *   - discount_amount (float)
     *   - tax_amount (float)
     *   - tax_rate (float)
     *   - taxable_amount (float|null)
     *   - exempt_amount (float|null)
     *   - total (float)
     *   - exchange_rate (float|null)
     *   - buyer_rnc (string|null)
     *   - buyer_name (string|null)
     *   - buyer_email (string|null)
     *   - buyer_address (string|null)
     *   - tipo_ingresos (string: default '01')
     *   - modified_ncf (string|null)
     *   - modified_date (string|null)
     *   - modification_code (int|null)
     *   - modification_reason (string|null)
     *   - items (array of ['description', 'quantity', 'unit_price', 'amount', 'is_exempt', 'tax_rate', 'tax_amount'])
     * @param array $settings Emitter configuration array
     */
    public function buildFromArray(array $document, array $settings): string
    {
        $tipoECF = (int) ($document['ecf_type'] ?? 32);
        $eNCF = trim((string) ($document['encf'] ?? ''));

        if ($tipoECF <= 0 || $eNCF === '') {
            throw new Exception("El documento no tiene configurado TipoeCF o eNCF válido.");
        }

        // 1. Emitter details
        $rncEmisor = preg_replace('/[^0-9]/', '', (string) ($settings['company_tax_id'] ?? ''));
        $razonSocialEmisor = trim((string) ($settings['dgii_razon_social'] ?? '')) ?: trim((string) ($settings['company_name'] ?? 'GridBase'));
        $nombreComercial = trim((string) ($settings['dgii_nombre_comercial'] ?? ''));
        $direccionEmisor = trim((string) ($settings['company_address'] ?? 'Santo Domingo, Rep. Dom.'));
        $municipioEmisor = trim((string) ($settings['dgii_municipio'] ?? ''));
        $provinciaEmisor = trim((string) ($settings['dgii_provincia'] ?? ''));
        $rawPhone = preg_replace('/[^0-9]/', '', (string) ($settings['company_phone'] ?? ''));
        $telefonoEmisor = strlen($rawPhone) === 10
            ? substr($rawPhone, 0, 3) . '-' . substr($rawPhone, 3, 3) . '-' . substr($rawPhone, 6, 4)
            : $rawPhone;
        $correoEmisor = trim((string) ($settings['company_email'] ?? ''));
        $webSiteEmisor = trim((string) ($settings['company_website'] ?? ''));

        if ($rncEmisor === '') {
            throw new Exception("El RNC del Emisor es obligatorio en la configuración fiscal.");
        }

        // 2. Buyer details
        $rncComprador = preg_replace('/[^0-9]/', '', (string) ($document['buyer_rnc'] ?? ''));
        $razonSocialComprador = trim((string) ($document['buyer_name'] ?? 'Cliente General'));

        $buyerRncRequired = [31, 33, 34, 41, 44, 45, 46];
        if (in_array($tipoECF, $buyerRncRequired, true) && $rncComprador === '') {
            throw new Exception("El RNC del Comprador es obligatorio para e-CF tipo {$tipoECF}.");
        }

        // 3. Dates & sequence expiration
        $storedExpiry = $settings["dgii_ncf_expiry_date_{$tipoECF}"] ?? $settings['dgii_ncf_expiry_date'] ?? ($document['ncf_expiration'] ?? null);
        if ($storedExpiry && strtotime((string) $storedExpiry) >= strtotime('today')) {
            $fechaVencimientoSecuencia = (string) $storedExpiry;
        } else {
            $fechaVencimientoSecuencia = ((int) date('Y') + 1) . '-12-31';
        }

        $issueDateFormatted = $this->formatDateDmg($document['issue_date'] ?? date('Y-m-d'));
        $dueDateFormatted = !empty($document['due_date']) ? $this->formatDateDmg($document['due_date']) : null;
        $isCredito = !empty($document['due_date']) && strtotime((string) $document['due_date']) > strtotime((string) ($document['issue_date'] ?? 'today'));
        $tipoPago = $isCredito ? 2 : 1;

        // 4. Totals
        $rate = (float) ($document['exchange_rate'] ?? 1.0);
        if ($rate <= 0) {
            $rate = 1.0;
        }

        $subtotal = (float) ($document['subtotal'] ?? 0.0) * $rate;
        $discountTotal = (float) ($document['discount_amount'] ?? 0.0) * $rate;
        $totalITBIS = (float) ($document['tax_amount'] ?? 0.0) * $rate;
        $montoTotal = (float) ($document['total'] ?? ($subtotal - $discountTotal + $totalITBIS)) * $rate;

        $montoGravadoI1 = 0.0;
        $montoGravadoI2 = 0.0;
        $montoGravadoI3 = 0.0;
        $montoExento = 0.0;
        $totalITBIS1 = 0.0;
        $totalITBIS2 = 0.0;
        $totalITBIS3 = 0.0;

        $items = $document['items'] ?? [];
        $hasLineTaxes = false;
        foreach ($items as $it) {
            if (isset($it['is_exempt']) || isset($it['tax_rate'])) {
                $hasLineTaxes = true;
                break;
            }
        }

        if ($hasLineTaxes) {
            foreach ($items as $it) {
                $itemAmount = (float) ($it['amount'] ?? 0.0) * $rate;
                $itemDiscount = ($subtotal > 0 && $discountTotal > 0) ? ($discountTotal * ($itemAmount / $subtotal)) : 0.0;
                $netItemAmount = max(0.0, $itemAmount - $itemDiscount);

                $itRate = isset($it['tax_rate']) ? (float) $it['tax_rate'] : (float) ($document['tax_rate'] ?? 0.0);
                $itExempt = isset($it['is_exempt']) ? (bool) $it['is_exempt'] : ($itRate == 0.0);

                if ($tipoECF === 46) {
                    $montoGravadoI3 += $netItemAmount;
                } elseif ($itExempt || $itRate == 0.0) {
                    $montoExento += $netItemAmount;
                } elseif ((int) round($itRate) === 16) {
                    $montoGravadoI2 += $netItemAmount;
                    $lineTax = isset($it['tax_amount']) ? ((float) $it['tax_amount'] * $rate) : ($netItemAmount * 0.16);
                    $totalITBIS2 += $lineTax;
                } else {
                    $montoGravadoI1 += $netItemAmount;
                    $lineTax = isset($it['tax_amount']) ? ((float) $it['tax_amount'] * $rate) : ($netItemAmount * 0.18);
                    $totalITBIS1 += $lineTax;
                }
            }

            $montoGravadoTotal = $montoGravadoI1 + $montoGravadoI2 + $montoGravadoI3;
            if ($totalITBIS1 > 0 && $totalITBIS2 == 0) {
                $totalITBIS1 = $totalITBIS ?: $totalITBIS1;
                $totalITBIS = $totalITBIS1;
            }
        } else {
            $montoGravadoTotal = 0.0;
            if ((float) ($document['taxable_amount'] ?? 0) > 0 || (float) ($document['exempt_amount'] ?? 0) > 0) {
                $montoGravadoTotal = (float) ($document['taxable_amount'] ?? 0) * $rate;
                $montoGravadoI1 = $montoGravadoTotal;
                $montoExento = (float) ($document['exempt_amount'] ?? 0) * $rate;
                $totalITBIS1 = $totalITBIS;
            } elseif ((float) ($document['tax_rate'] ?? 0) > 0 || $totalITBIS > 0) {
                $montoGravadoTotal = max(0.0, $subtotal - $discountTotal);
                $montoGravadoI1 = $montoGravadoTotal;
                $totalITBIS1 = $totalITBIS;
            } else {
                $montoExento = max(0.0, $subtotal - $discountTotal);
            }
        }

        if (in_array($tipoECF, [43, 44, 47], true)) {
            $montoExento = max(0.0, $subtotal - $discountTotal);
            $montoGravadoTotal = 0.0;
            $montoGravadoI1 = 0.0;
            $montoGravadoI2 = 0.0;
            $montoGravadoI3 = 0.0;
            $totalITBIS = 0.0;
            $totalITBIS1 = 0.0;
            $totalITBIS2 = 0.0;
            $totalITBIS3 = 0.0;
        } elseif ($tipoECF === 46) {
            $montoGravadoI3 = max(0.0, $subtotal - $discountTotal);
            $montoGravadoTotal = $montoGravadoI3;
            $montoExento = 0.0;
            $totalITBIS = 0.0;
            $totalITBIS1 = 0.0;
            $totalITBIS2 = 0.0;
            $totalITBIS3 = 0.0;
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        $root = $dom->createElement('ECF');
        $dom->appendChild($root);

        $encabezado = $dom->createElement('Encabezado');
        $root->appendChild($encabezado);
        $encabezado->appendChild($dom->createElement('Version', '1.0'));

        $idDoc = $dom->createElement('IdDoc');
        $encabezado->appendChild($idDoc);
        $idDoc->appendChild($dom->createElement('TipoeCF', (string) $tipoECF));
        $idDoc->appendChild($dom->createElement('eNCF', $eNCF));

        if (!in_array($tipoECF, [32, 34], true)) {
            $idDoc->appendChild($dom->createElement('FechaVencimientoSecuencia', $this->formatDateDmg($fechaVencimientoSecuencia)));
        }

        if ($tipoECF === 34) {
            $idDoc->appendChild($dom->createElement('IndicadorNotaCredito', (string) ($document['nota_credito_indicator'] ?? 0)));
        }

        if (in_array($tipoECF, [31, 32, 33, 34, 41, 45], true)) {
            $idDoc->appendChild($dom->createElement('IndicadorMontoGravado', '0'));
        }

        if (in_array($tipoECF, [31, 32, 33, 34, 44, 45, 46], true)) {
            $idDoc->appendChild($dom->createElement('TipoIngresos', (string) ($document['tipo_ingresos'] ?? '01')));
        }

        $idDoc->appendChild($dom->createElement('TipoPago', (string) ($tipoPago ?: 1)));

        if ($tipoPago === 2 && $dueDateFormatted && $tipoECF !== 43) {
            $idDoc->appendChild($dom->createElement('FechaLimitePago', $dueDateFormatted));
        }

        $emisor = $dom->createElement('Emisor');
        $encabezado->appendChild($emisor);
        $emisor->appendChild($dom->createElement('RNCEmisor', $rncEmisor));
        $emisor->appendChild($dom->createElement('RazonSocialEmisor', htmlspecialchars($razonSocialEmisor, ENT_XML1)));

        if ($nombreComercial !== '') {
            $emisor->appendChild($dom->createElement('NombreComercial', htmlspecialchars(substr($nombreComercial, 0, 150), ENT_XML1)));
        }

        $emisor->appendChild($dom->createElement('DireccionEmisor', htmlspecialchars(substr($direccionEmisor ?: 'Santo Domingo', 0, 100), ENT_XML1)));

        if ($municipioEmisor !== '') {
            $emisor->appendChild($dom->createElement('Municipio', $municipioEmisor));
        }
        if ($provinciaEmisor !== '') {
            $emisor->appendChild($dom->createElement('Provincia', $provinciaEmisor));
        }
        if ($telefonoEmisor !== '') {
            $tablaTelefono = $dom->createElement('TablaTelefonoEmisor');
            $emisor->appendChild($tablaTelefono);
            $tablaTelefono->appendChild($dom->createElement('TelefonoEmisor', htmlspecialchars(substr($telefonoEmisor, 0, 12), ENT_XML1)));
        }
        if ($correoEmisor !== '') {
            $emisor->appendChild($dom->createElement('CorreoEmisor', htmlspecialchars($correoEmisor, ENT_XML1)));
        }
        if ($webSiteEmisor !== '') {
            $emisor->appendChild($dom->createElement('WebSite', htmlspecialchars(substr($webSiteEmisor, 0, 50), ENT_XML1)));
        }

        $emisor->appendChild($dom->createElement('FechaEmision', $issueDateFormatted));

        if ($tipoECF !== 43) {
            $comprador = $dom->createElement('Comprador');
            $encabezado->appendChild($comprador);

            if ($tipoECF === 47) {
                $comprador->appendChild($dom->createElement('IdentificadorExtranjero', $rncComprador ?: '00000000000'));
            } elseif ($rncComprador !== '') {
                $comprador->appendChild($dom->createElement('RNCComprador', $rncComprador));
            }

            $comprador->appendChild($dom->createElement('RazonSocialComprador', htmlspecialchars($razonSocialComprador ?: 'Cliente General', ENT_XML1)));

            if (!empty($document['buyer_email']) && $tipoECF !== 47) {
                $comprador->appendChild($dom->createElement('CorreoComprador', htmlspecialchars(substr((string) $document['buyer_email'], 0, 80), ENT_XML1)));
            }
            if (!empty($document['buyer_address']) && $tipoECF !== 47) {
                $comprador->appendChild($dom->createElement('DireccionComprador', htmlspecialchars(substr((string) $document['buyer_address'], 0, 100), ENT_XML1)));
            }
        }

        $totales = $dom->createElement('Totales');
        $encabezado->appendChild($totales);

        if ($tipoECF === 43) {
            $totales->appendChild($dom->createElement('MontoExento', number_format($subtotal - $discountTotal, 2, '.', '')));
            $totales->appendChild($dom->createElement('MontoTotal', number_format($montoTotal, 2, '.', '')));
        } elseif ($tipoECF === 47) {
            $totales->appendChild($dom->createElement('MontoExento', number_format($subtotal - $discountTotal, 2, '.', '')));
            $totales->appendChild($dom->createElement('MontoTotal', number_format($montoTotal, 2, '.', '')));
            $totales->appendChild($dom->createElement('TotalISRRetencion', '0.00'));
        } elseif ($tipoECF === 44) {
            $totales->appendChild($dom->createElement('MontoExento', number_format($subtotal - $discountTotal, 2, '.', '')));
            $totales->appendChild($dom->createElement('MontoTotal', number_format($subtotal - $discountTotal, 2, '.', '')));
        } elseif ($tipoECF === 46) {
            $netAmount = $subtotal - $discountTotal;
            $totales->appendChild($dom->createElement('MontoGravadoTotal', number_format($netAmount, 2, '.', '')));
            $totales->appendChild($dom->createElement('MontoGravadoI3', number_format($netAmount, 2, '.', '')));
            $totales->appendChild($dom->createElement('ITBIS3', '0'));
            $totales->appendChild($dom->createElement('TotalITBIS', '0.00'));
            $totales->appendChild($dom->createElement('TotalITBIS3', '0.00'));
            $totales->appendChild($dom->createElement('MontoTotal', number_format($netAmount, 2, '.', '')));
        } else {
            if ($montoGravadoTotal > 0) {
                $totales->appendChild($dom->createElement('MontoGravadoTotal', number_format($montoGravadoTotal, 2, '.', '')));
                if ($montoGravadoI1 > 0) {
                    $totales->appendChild($dom->createElement('MontoGravadoI1', number_format($montoGravadoI1, 2, '.', '')));
                }
                if ($montoGravadoI2 > 0) {
                    $totales->appendChild($dom->createElement('MontoGravadoI2', number_format($montoGravadoI2, 2, '.', '')));
                }
                if ($montoGravadoI3 > 0) {
                    $totales->appendChild($dom->createElement('MontoGravadoI3', number_format($montoGravadoI3, 2, '.', '')));
                }
            }

            if ($montoExento > 0) {
                $totales->appendChild($dom->createElement('MontoExento', number_format($montoExento, 2, '.', '')));
            }

            if ($montoGravadoI1 > 0) {
                $totales->appendChild($dom->createElement('ITBIS1', '18'));
            }
            if ($montoGravadoI2 > 0) {
                $totales->appendChild($dom->createElement('ITBIS2', '16'));
            }
            if ($montoGravadoI3 > 0) {
                $totales->appendChild($dom->createElement('ITBIS3', '0'));
            }

            if ($totalITBIS > 0) {
                $totales->appendChild($dom->createElement('TotalITBIS', number_format($totalITBIS, 2, '.', '')));
                if ($totalITBIS1 > 0 || ($montoGravadoI1 > 0 && $totalITBIS2 == 0)) {
                    $totales->appendChild($dom->createElement('TotalITBIS1', number_format($totalITBIS1 ?: $totalITBIS, 2, '.', '')));
                }
                if ($totalITBIS2 > 0) {
                    $totales->appendChild($dom->createElement('TotalITBIS2', number_format($totalITBIS2, 2, '.', '')));
                }
                if ($totalITBIS3 > 0) {
                    $totales->appendChild($dom->createElement('TotalITBIS3', number_format($totalITBIS3, 2, '.', '')));
                }
            }

            $totales->appendChild($dom->createElement('MontoTotal', number_format($montoTotal, 2, '.', '')));

            if ($tipoECF === 41) {
                $totales->appendChild($dom->createElement('TotalITBISRetenido', '0.00'));
                $totales->appendChild($dom->createElement('TotalISRRetencion', '0.00'));
            }
        }

        $detallesItems = $dom->createElement('DetallesItems');
        $root->appendChild($detallesItems);

        $lineNum = 1;
        foreach ($items as $item) {
            $itemNode = $dom->createElement('Item');
            $detallesItems->appendChild($itemNode);

            $itemNode->appendChild($dom->createElement('NumeroLinea', (string) $lineNum));

            if (in_array($tipoECF, [43, 44, 47], true)) {
                $indicadorFact = 4;
            } elseif ($tipoECF === 46) {
                $indicadorFact = 3;
            } elseif (!empty($item['is_exempt'])) {
                $indicadorFact = 4;
            } elseif (isset($item['tax_rate'])) {
                $rateVal = (float) $item['tax_rate'];
                if ($rateVal == 0.0) {
                    $indicadorFact = 4;
                } elseif ((int) round($rateVal) === 16) {
                    $indicadorFact = 2;
                } else {
                    $indicadorFact = 1;
                }
            } else {
                $indicadorFact = ((float) ($document['tax_rate'] ?? 0.0)) > 0 ? 1 : 4;
            }

            $itemNode->appendChild($dom->createElement('IndicadorFacturacion', (string) $indicadorFact));

            if (in_array($tipoECF, [41, 47], true)) {
                $retencion = $dom->createElement('Retencion');
                $itemNode->appendChild($retencion);
                $retencion->appendChild($dom->createElement('IndicadorAgenteRetencionoPercepcion', '1'));
                if ($tipoECF === 41) {
                    $retencion->appendChild($dom->createElement('MontoITBISRetenido', '0.00'));
                    $retencion->appendChild($dom->createElement('MontoISRRetenido', '0.00'));
                }
                if ($tipoECF === 47) {
                    $retencion->appendChild($dom->createElement('MontoISRRetenido', '0.00'));
                }
            }

            $itemNode->appendChild($dom->createElement('NombreItem', htmlspecialchars(substr((string) ($item['description'] ?? 'Servicio Educativo'), 0, 80), ENT_XML1)));
            $itemNode->appendChild($dom->createElement('IndicadorBienoServicio', '2'));
            $itemNode->appendChild($dom->createElement('CantidadItem', number_format((float) ($item['quantity'] ?? 1), 2, '.', '')));
            $itemNode->appendChild($dom->createElement('UnidadMedida', '43'));
            $itemNode->appendChild($dom->createElement('PrecioUnitarioItem', number_format((float) ($item['unit_price'] ?? 0) * $rate, 4, '.', '')));
            $itemNode->appendChild($dom->createElement('MontoItem', number_format((float) ($item['amount'] ?? 0) * $rate, 2, '.', '')));

            $lineNum++;
        }

        if (in_array($tipoECF, [33, 34], true)) {
            $infoReferencia = $dom->createElement('InformacionReferencia');
            $root->appendChild($infoReferencia);

            $ncfModificado = (string) ($document['modified_ncf'] ?? 'E310000000000');
            $infoReferencia->appendChild($dom->createElement('NCFModificado', $ncfModificado));
            $infoReferencia->appendChild($dom->createElement('FechaNCFModificado', $this->formatDateDmg($document['modified_date'] ?? ($document['issue_date'] ?? date('Y-m-d')))));
            $infoReferencia->appendChild($dom->createElement('CodigoModificacion', (string) ($document['modification_code'] ?? 1)));

            if (!empty($document['modification_reason'])) {
                $infoReferencia->appendChild($dom->createElement('RazonModificacion', htmlspecialchars(substr((string) $document['modification_reason'], 0, 90), ENT_XML1)));
            }
        }

        $tz = new \DateTimeZone('America/Santo_Domingo');
        $nowDo = (new \DateTimeImmutable('now', $tz))->format('d-m-Y H:i:s');
        $root->appendChild($dom->createElement('FechaHoraFirma', $nowDo));

        return (string) $dom->saveXML();
    }

    /**
     * Builds the RFCE (Resumen Factura de Consumo Electrónica) XML for FC < 250k.
     */
    public function buildRfceFromArray(array $document, array $settings): string
    {
        $rncEmisor = preg_replace('/[^0-9]/', '', (string) ($settings['company_tax_id'] ?? ''));
        $razonSocialEmisor = trim((string) ($settings['dgii_razon_social'] ?? '')) ?: trim((string) ($settings['company_name'] ?? 'GridBase'));
        $rncComprador = preg_replace('/[^0-9]/', '', (string) ($document['buyer_rnc'] ?? ''));
        $razonSocialComprador = trim((string) ($document['buyer_name'] ?? 'Cliente General'));

        $isCredito = !empty($document['due_date']) && strtotime((string) $document['due_date']) > strtotime((string) ($document['issue_date'] ?? 'today'));
        $tipoPago = $isCredito ? 2 : 1;

        $rate = (float) ($document['exchange_rate'] ?? 1.0);
        if ($rate <= 0) {
            $rate = 1.0;
        }

        $subtotal = (float) ($document['subtotal'] ?? 0.0) * $rate;
        $discountTotal = (float) ($document['discount_amount'] ?? 0.0) * $rate;
        $totalITBIS = (float) ($document['tax_amount'] ?? 0.0) * $rate;
        $montoTotal = (float) ($document['total'] ?? ($subtotal - $discountTotal + $totalITBIS)) * $rate;

        $montoGravadoTotal = $totalITBIS > 0 ? max(0.0, $subtotal - $discountTotal) : 0.0;
        $montoExento = $totalITBIS > 0 ? 0.0 : max(0.0, $subtotal - $discountTotal);

        $fechaEmision = $this->formatDateDmg($document['issue_date'] ?? date('Y-m-d'));

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        $root = $dom->createElement('RFCE');
        $dom->appendChild($root);

        $encabezado = $dom->createElement('Encabezado');
        $root->appendChild($encabezado);
        $encabezado->appendChild($dom->createElement('Version', '1.0'));

        $idDoc = $dom->createElement('IdDoc');
        $encabezado->appendChild($idDoc);
        $idDoc->appendChild($dom->createElement('TipoeCF', '32'));
        $idDoc->appendChild($dom->createElement('eNCF', (string) ($document['encf'] ?? '')));
        $idDoc->appendChild($dom->createElement('TipoIngresos', (string) ($document['tipo_ingresos'] ?? '01')));
        $idDoc->appendChild($dom->createElement('TipoPago', (string) $tipoPago));

        $emisor = $dom->createElement('Emisor');
        $encabezado->appendChild($emisor);
        $emisor->appendChild($dom->createElement('RNCEmisor', $rncEmisor));
        $emisor->appendChild($dom->createElement('RazonSocialEmisor', htmlspecialchars($razonSocialEmisor, ENT_XML1)));
        $emisor->appendChild($dom->createElement('FechaEmision', $fechaEmision));

        $comprador = $dom->createElement('Comprador');
        $encabezado->appendChild($comprador);
        if ($rncComprador !== '') {
            $comprador->appendChild($dom->createElement('RNCComprador', $rncComprador));
        }
        if ($razonSocialComprador !== '') {
            $comprador->appendChild($dom->createElement('RazonSocialComprador', htmlspecialchars($razonSocialComprador, ENT_XML1)));
        }

        $totales = $dom->createElement('Totales');
        $encabezado->appendChild($totales);
        if ($montoGravadoTotal > 0) {
            $totales->appendChild($dom->createElement('MontoGravadoTotal', number_format($montoGravadoTotal, 2, '.', '')));
            $totales->appendChild($dom->createElement('MontoGravadoI1', number_format($montoGravadoTotal, 2, '.', '')));
        }
        if ($montoExento > 0) {
            $totales->appendChild($dom->createElement('MontoExento', number_format($montoExento, 2, '.', '')));
        }
        if ($totalITBIS > 0) {
            $totales->appendChild($dom->createElement('TotalITBIS', number_format($totalITBIS, 2, '.', '')));
            $totales->appendChild($dom->createElement('TotalITBIS1', number_format($totalITBIS, 2, '.', '')));
        }
        $totales->appendChild($dom->createElement('MontoTotal', number_format($montoTotal, 2, '.', '')));

        $securityCode = (string) ($document['security_code'] ?? '000000');
        $encabezado->appendChild($dom->createElement('CodigoSeguridadeCF', $securityCode));

        return (string) $dom->saveXML();
    }

    private function formatDateDmg($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('d-m-Y');
        }
        $str = trim((string) $date);
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $str)) {
            return $str;
        }
        $ts = strtotime($str);

        return $ts ? date('d-m-Y', $ts) : date('d-m-Y');
    }
}
