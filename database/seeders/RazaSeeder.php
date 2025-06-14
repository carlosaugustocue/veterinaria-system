<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especie;
use App\Models\Raza;

class RazaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener especies
        $perro = Especie::where('nombre', Especie::PERRO)->first();
        $gato = Especie::where('nombre', Especie::GATO)->first();
        $ave = Especie::where('nombre', Especie::AVE)->first();
        $conejo = Especie::where('nombre', Especie::CONEJO)->first();
        $reptil = Especie::where('nombre', Especie::REPTIL)->first();
        $roedor = Especie::where('nombre', Especie::ROEDOR)->first();

        $razas = [];

        // RAZAS DE PERROS
        if ($perro) {
            $razasPerros = [
                [
                    'nombre' => 'Labrador Retriever',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro amigable, inteligente y activo. Excelente para familias.',
                    'tamano' => Raza::TAMANO_GRANDE,
                    'peso_promedio_min' => 25,
                    'peso_promedio_max' => 36,
                    'esperanza_vida_min' => 10,
                    'esperanza_vida_max' => 14,
                    'colores_comunes' => ['Amarillo', 'Negro', 'Chocolate'],
                    'origen_pais' => 'Canadá'
                ],
                [
                    'nombre' => 'Golden Retriever',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro leal, inteligente y cariñoso. Ideal para terapia y como mascota familiar.',
                    'tamano' => Raza::TAMANO_GRANDE,
                    'peso_promedio_min' => 25,
                    'peso_promedio_max' => 34,
                    'esperanza_vida_min' => 10,
                    'esperanza_vida_max' => 12,
                    'colores_comunes' => ['Dorado claro', 'Dorado', 'Dorado oscuro'],
                    'origen_pais' => 'Escocia'
                ],
                [
                    'nombre' => 'Pastor Alemán',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro versátil, inteligente y leal. Excelente perro de trabajo y guardián.',
                    'tamano' => Raza::TAMANO_GRANDE,
                    'peso_promedio_min' => 22,
                    'peso_promedio_max' => 40,
                    'esperanza_vida_min' => 9,
                    'esperanza_vida_max' => 13,
                    'colores_comunes' => ['Negro y fuego', 'Sable', 'Negro sólido'],
                    'origen_pais' => 'Alemania'
                ],
                [
                    'nombre' => 'Bulldog Francés',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro pequeño, cariñoso y adaptable. Perfecto para apartamentos.',
                    'tamano' => Raza::TAMANO_PEQUENO,
                    'peso_promedio_min' => 8,
                    'peso_promedio_max' => 14,
                    'esperanza_vida_min' => 10,
                    'esperanza_vida_max' => 14,
                    'colores_comunes' => ['Atigrado', 'Crema', 'Blanco'],
                    'cuidados_especiales' => 'Sensible al calor, problemas respiratorios',
                    'origen_pais' => 'Francia'
                ],
                [
                    'nombre' => 'Chihuahua',
                    'especie_id' => $perro->id,
                    'descripcion' => 'La raza de perro más pequeña del mundo. Valiente y alerta.',
                    'tamano' => Raza::TAMANO_MUY_PEQUENO,
                    'peso_promedio_min' => 1.5,
                    'peso_promedio_max' => 3,
                    'esperanza_vida_min' => 14,
                    'esperanza_vida_max' => 18,
                    'colores_comunes' => ['Variados'],
                    'cuidados_especiales' => 'Sensible al frío, frágil',
                    'origen_pais' => 'México'
                ],
                [
                    'nombre' => 'Poodle',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro inteligente, hipoalergénico y elegante. Viene en varios tamaños.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 6,
                    'peso_promedio_max' => 32,
                    'esperanza_vida_min' => 12,
                    'esperanza_vida_max' => 15,
                    'colores_comunes' => ['Negro', 'Blanco', 'Marrón', 'Gris'],
                    'caracteristicas_especiales' => 'Hipoalergénico, no pierde pelo',
                    'origen_pais' => 'Francia'
                ],
                [
                    'nombre' => 'Mestizo',
                    'especie_id' => $perro->id,
                    'descripcion' => 'Perro de raza mixta. Cada uno es único en apariencia y temperamento.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 5,
                    'peso_promedio_max' => 40,
                    'esperanza_vida_min' => 10,
                    'esperanza_vida_max' => 16,
                    'colores_comunes' => ['Variados'],
                    'caracteristicas_especiales' => 'Gran diversidad genética, generalmente saludables'
                ]
            ];

            $razas = array_merge($razas, $razasPerros);
        }

        // RAZAS DE GATOS
        if ($gato) {
            $razasGatos = [
                [
                    'nombre' => 'Persa',
                    'especie_id' => $gato->id,
                    'descripcion' => 'Gato de pelo largo, tranquilo y elegante.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 3,
                    'peso_promedio_max' => 7,
                    'esperanza_vida_min' => 12,
                    'esperanza_vida_max' => 18,
                    'colores_comunes' => ['Blanco', 'Negro', 'Gris', 'Crema'],
                    'cuidados_especiales' => 'Cepillado diario, cuidado ocular',
                    'origen_pais' => 'Irán'
                ],
                [
                    'nombre' => 'Siamés',
                    'especie_id' => $gato->id,
                    'descripcion' => 'Gato vocal, social e inteligente con puntos de color distintivos.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 3,
                    'peso_promedio_max' => 5,
                    'esperanza_vida_min' => 15,
                    'esperanza_vida_max' => 20,
                    'colores_comunes' => ['Seal point', 'Blue point', 'Chocolate point'],
                    'caracteristicas_especiales' => 'Muy vocal, ojos azules',
                    'origen_pais' => 'Tailandia'
                ],
                [
                    'nombre' => 'Maine Coon',
                    'especie_id' => $gato->id,
                    'descripcion' => 'Una de las razas de gatos domésticos más grandes, gentil y amigable.',
                    'tamano' => Raza::TAMANO_GRANDE,
                    'peso_promedio_min' => 4,
                    'peso_promedio_max' => 10,
                    'esperanza_vida_min' => 13,
                    'esperanza_vida_max' => 16,
                    'colores_comunes' => ['Tabby', 'Sólidos', 'Bicolores'],
                    'caracteristicas_especiales' => 'Muy grande, pelo semi-largo',
                    'origen_pais' => 'Estados Unidos'
                ],
                [
                    'nombre' => 'Doméstico Común',
                    'especie_id' => $gato->id,
                    'descripcion' => 'Gato sin pedigrí, resistente y adaptable.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 3,
                    'peso_promedio_max' => 6,
                    'esperanza_vida_min' => 13,
                    'esperanza_vida_max' => 17,
                    'colores_comunes' => ['Variados'],
                    'caracteristicas_especiales' => 'Gran diversidad, generalmente saludables'
                ],
                [
                    'nombre' => 'Británico de Pelo Corto',
                    'especie_id' => $gato->id,
                    'descripcion' => 'Gato robusto, tranquilo y de buen carácter.',
                    'tamano' => Raza::TAMANO_MEDIANO,
                    'peso_promedio_min' => 4,
                    'peso_promedio_max' => 8,
                    'esperanza_vida_min' => 14,
                    'esperanza_vida_max' => 20,
                    'colores_comunes' => ['Azul', 'Negro', 'Blanco', 'Crema'],
                    'origen_pais' => 'Reino Unido'
                ]
            ];

            $razas = array_merge($razas, $razasGatos);
        }

        // OTRAS ESPECIES
        if ($ave) {
            $razas[] = [
                'nombre' => 'Canario',
                'especie_id' => $ave->id,
                'descripcion' => 'Ave pequeña conocida por su canto melodioso.',
                'tamano' => Raza::TAMANO_MUY_PEQUENO,
                'esperanza_vida_min' => 10,
                'esperanza_vida_max' => 15,
                'colores_comunes' => ['Amarillo', 'Naranja', 'Blanco'],
                'origen_pais' => 'Islas Canarias'
            ];

            $razas[] = [
                'nombre' => 'Periquito',
                'especie_id' => $ave->id,
                'descripcion' => 'Ave pequeña, colorida y social, fácil de cuidar.',
                'tamano' => Raza::TAMANO_MUY_PEQUENO,
                'esperanza_vida_min' => 5,
                'esperanza_vida_max' => 10,
                'colores_comunes' => ['Verde', 'Azul', 'Amarillo'],
                'origen_pais' => 'Australia'
            ];
        }

        if ($conejo) {
            $razas[] = [
                'nombre' => 'Holandés Enano',
                'especie_id' => $conejo->id,
                'descripcion' => 'Conejo pequeño y dócil, ideal como mascota.',
                'tamano' => Raza::TAMANO_PEQUENO,
                'peso_promedio_min' => 0.5,
                'peso_promedio_max' => 1.2,
                'esperanza_vida_min' => 8,
                'esperanza_vida_max' => 12,
                'colores_comunes' => ['Variados'],
                'origen_pais' => 'Países Bajos'
            ];
        }

        if ($roedor) {
            $razas[] = [
                'nombre' => 'Hámster Sirio',
                'especie_id' => $roedor->id,
                'descripcion' => 'Roedor pequeño, solitario y fácil de cuidar.',
                'tamano' => Raza::TAMANO_MUY_PEQUENO,
                'peso_promedio_min' => 0.1,
                'peso_promedio_max' => 0.2,
                'esperanza_vida_min' => 2,
                'esperanza_vida_max' => 4,
                'colores_comunes' => ['Dorado', 'Blanco', 'Gris'],
                'origen_pais' => 'Siria'
            ];
        }

        // Crear todas las razas
        foreach ($razas as $razaData) {
            Raza::create($razaData);
        }

        $this->command->info('✅ Razas creadas exitosamente:');
        $this->command->info("   🐕 Perros: " . collect($razas)->where('especie_id', $perro?->id)->count() . " razas");
        $this->command->info("   🐱 Gatos: " . collect($razas)->where('especie_id', $gato?->id)->count() . " razas");
        $this->command->info("   🐦 Aves: " . collect($razas)->where('especie_id', $ave?->id)->count() . " razas");
        $this->command->info("   🐰 Conejos: " . collect($razas)->where('especie_id', $conejo?->id)->count() . " razas");
        $this->command->info("   🐹 Roedores: " . collect($razas)->where('especie_id', $roedor?->id)->count() . " razas");
        $this->command->info("   📊 Total: " . count($razas) . " razas");
    }
}