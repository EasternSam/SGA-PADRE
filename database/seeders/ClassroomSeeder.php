<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Building;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $json = '{
          "inventario_general": {
            "fecha_generacion": "2026-01-21",
            "edificios": [
              {
                "nombre": "Edificio A",
                "espacios": [
                  { "id": "Lab. 01", "capacidad": 31, "mesas": 8, "otros_equipos": "Televisor" },
                  { "id": "Lab. 02", "capacidad": 30, "mesas": 14, "otros_equipos": null },
                  { "id": "Lab. 04", "capacidad": 31, "mesas": 12, "otros_equipos": null },
                  { "id": "Aula 303", "capacidad": 31, "mesas": 9, "otros_equipos": "Televisor" },
                  { "id": "Aula 304", "capacidad": 27, "mesas": 11, "otros_equipos": "Televisor" },
                  { "id": "Aula 307", "capacidad": 23, "mesas": 1, "otros_equipos": "Televisor" },
                  { "id": "Aula 402", "capacidad": 33, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 403", "capacidad": 30, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 404", "capacidad": 20, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 405", "capacidad": 30, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 406", "capacidad": 39, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 407", "capacidad": 30, "mesas": 11, "otros_equipos": null },
                  { "id": "Aula 408", "capacidad": 26, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 501", "capacidad": 11, "mesas": 3, "otros_equipos": "Televisor" },
                  { "id": "Aula 502", "capacidad": 25, "mesas": 10, "otros_equipos": "Televisor" },
                  { "id": "Aula 503", "capacidad": 29, "mesas": 13, "otros_equipos": "Televisor" },
                  { "id": "Aula 504", "capacidad": 32, "mesas": 11, "otros_equipos": "Televisor" },
                  { "id": "Aula 601", "capacidad": 27, "mesas": 1, "otros_equipos": null },
                  { "id": "Aula 602", "capacidad": 47, "mesas": 3, "otros_equipos": null }
                ]
              },
              {
                "nombre": "Edificio B",
                "espacios": [
                  { "id": "Aula 101", "capacidad": 22, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 103", "capacidad": 22, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 104", "capacidad": 22, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 106", "capacidad": 22, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 202", "capacidad": 24, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 203", "capacidad": 38, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma (dañado), Proyector" },
                  { "id": "Aula 204", "capacidad": 28, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 205", "capacidad": 24, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 206", "capacidad": 35, "mesas": 0, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 301", "capacidad": 37, "mesas": 0, "otros_equipos": "Pizarra, Proyector, Juego de bocinas" },
                  { "id": "Aula 302", "capacidad": 120, "mesas": 0, "otros_equipos": "Proyector, Juego de bocinas" },
                  { "id": "Aula 303", "capacidad": 32, "mesas": 11, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 401 (Caja)", "capacidad": 71, "mesas": 22, "otros_equipos": "35 Computadoras, Pizarra" },
                  { "id": "Aula 402", "capacidad": 42, "mesas": 9, "otros_equipos": "Pizarra, TV Plasma" },
                  { "id": "Aula 403", "capacidad": 46, "mesas": 11, "otros_equipos": "Pizarra" }
                ]
              },
              {
                "nombre": "Edificio B1",
                "espacios": [
                  { "id": "Aula B1-01", "capacidad": 17 },
                  { "id": "Aula B1-02", "capacidad": 25 },
                  { "id": "Aula B1-03", "capacidad": 19 },
                  { "id": "Aula B1-04", "capacidad": 25 },
                  { "id": "Aula B1-05", "capacidad": 19 },
                  { "id": "Aula B1-06", "capacidad": 26 },
                  { "id": "Aula B1-07", "capacidad": 20 },
                  { "id": "Aula B1-08", "capacidad": 28 }
                ]
              },
              {
                "nombre": "Edificio C (Tecnología)",
                "espacios": [
                  { "id": "Lab. 01", "computadoras_pc": 35, "capacidad": 32 },
                  { "id": "Lab. 02", "computadoras_pc": 40, "capacidad": 39 },
                  { "id": "Lab. 03", "computadoras_pc": 43, "capacidad": 43 },
                  { "id": "Lab. 04", "computadoras_pc": 42, "capacidad": 42 },
                  { "id": "Lab. 05", "computadoras_pc": 45, "capacidad": 45 },
                  { "id": "Lab. 06", "computadoras_pc": 28, "capacidad": 28 },
                  { "id": "Lab. 07", "computadoras_pc": 35, "capacidad": 35 },
                  { "id": "Lab. 08", "computadoras_pc": 36, "capacidad": 36 },
                  { "id": "Lab. 09", "computadoras_pc": 39, "capacidad": 39 },
                  { "id": "Lab. 10", "computadoras_pc": 29, "capacidad": 29 },
                  { "id": "Lab. 11", "computadoras_pc": 31, "capacidad": 31 },
                  { "id": "Aula 301", "computadoras_pc": 11, "capacidad": 27 },
                  { "id": "Lab. 12", "computadoras_pc": 40, "capacidad": 33 },
                  { "id": "Lab. 13", "computadoras_pc": 1, "capacidad": 53 },
                  { "id": "Lab. 14", "computadoras_pc": 48, "capacidad": 46 },
                  { "id": "Lab. 15", "computadoras_pc": 41, "capacidad": 41 },
                  { "id": "Lab. 16", "computadoras_pc": 39, "capacidad": 39 },
                  { "id": "Aula 401", "computadoras_pc": 1, "capacidad": 35 },
                  { "id": "Lab. 17", "computadoras_pc": 32, "capacidad": 31 },
                  { "id": "Lab. 18", "computadoras_pc": 0, "capacidad": 31 },
                  { "id": "Lab. 19", "computadoras_pc": 22, "capacidad": 11 },
                  { "id": "Lab. 20", "computadoras_pc": 21, "capacidad": 20 },
                  { "id": "Lab. 21", "computadoras_pc": 31, "capacidad": 29 },
                  { "id": "Lab. 22", "computadoras_pc": 0, "capacidad": 31 },
                  { "id": "Aula 501", "computadoras_pc": 1, "capacidad": 28 },
                  { "id": "Aula 502", "computadoras_pc": 0, "capacidad": 19 },
                  { "id": "Lab. 24", "computadoras_pc": 29, "capacidad": 30 },
                  { "id": "Lab. 25", "computadoras_pc": 19, "capacidad": 19 }
                ]
              }
            ]
          }
        }';

        $data = json_decode($json, true);

        foreach ($data['inventario_general']['edificios'] as $edificioData) {
            $building = Building::firstOrCreate(['name' => $edificioData['nombre']]);

            foreach ($edificioData['espacios'] as $espacio) {
                // Determinar tipo basado en el nombre o PC
                $type = 'Aula';
                if (str_contains(strtolower($espacio['id']), 'lab') || ($espacio['computadoras_pc'] ?? 0) > 5) {
                    $type = 'Laboratorio';
                }

                $equipment = [];
                if (!empty($espacio['otros_equipos'])) {
                    $equipment[] = $espacio['otros_equipos'];
                }
                if (!empty($espacio['mesas'])) {
                    $equipment[] = $espacio['mesas'] . ' Mesas';
                }

                Classroom::create([
                    'building_id' => $building->id,
                    'name' => $espacio['id'],
                    'capacity' => $espacio['capacidad'] ?? 0,
                    'pc_count' => $espacio['computadoras_pc'] ?? 0,
                    'type' => $type,
                    'equipment' => implode(', ', $equipment),
                ]);
            }
        }
    }
}