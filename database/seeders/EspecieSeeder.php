<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especie;

class EspecieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especies = [
            [
                'nombre' => Especie::PERRO,
                'nombre_cientifico' => 'Canis lupus familiaris',
                'descripcion' => 'Mamífero doméstico de la familia de los cánidos, conocido como el mejor amigo del hombre.',
                'icono' => '🐕'
            ],
            [
                'nombre' => Especie::GATO,
                'nombre_cientifico' => 'Felis catus',
                'descripcion' => 'Mamífero doméstico de la familia de los félidos, conocido por su independencia y agilidad.',
                'icono' => '🐱'
            ],
            [
                'nombre' => Especie::AVE,
                'nombre_cientifico' => 'Aves',
                'descripcion' => 'Clase de vertebrados con plumas, alas y pico. Incluye aves domésticas y de compañía.',
                'icono' => '🐦'
            ],
            [
                'nombre' => Especie::CONEJO,
                'nombre_cientifico' => 'Oryctolagus cuniculus',
                'descripcion' => 'Mamífero lagomorfo doméstico, popular como mascota por su carácter dócil.',
                'icono' => '🐰'
            ],
            [
                'nombre' => Especie::REPTIL,
                'nombre_cientifico' => 'Reptilia',
                'descripcion' => 'Clase de vertebrados de sangre fría que incluye serpientes, lagartos, tortugas e iguanas.',
                'icono' => '🦎'
            ],
            [
                'nombre' => Especie::PEZ,
                'nombre_cientifico' => 'Pisces',
                'descripcion' => 'Vertebrados acuáticos que respiran por branquias. Incluye peces de acuario y ornamentales.',
                'icono' => '🐠'
            ],
            [
                'nombre' => Especie::ROEDOR,
                'nombre_cientifico' => 'Rodentia',
                'descripcion' => 'Mamíferos caracterizados por tener dientes incisivos que crecen continuamente.',
                'icono' => '🐹'
            ],
            [
                'nombre' => 'Erizo',
                'nombre_cientifico' => 'Erinaceidae',
                'descripcion' => 'Pequeño mamífero cubierto de púas, cada vez más popular como mascota exótica.',
                'icono' => '🦔'
            ],
            [
                'nombre' => 'Hurón',
                'nombre_cientifico' => 'Mustela putorius furo',
                'descripcion' => 'Mamífero carnívoro domesticado, conocido por su naturaleza juguetona y curiosa.',
                'icono' => '🦦'
            ]
        ];

        foreach ($especies as $especieData) {
            Especie::create($especieData);
        }

        $this->command->info('✅ Especies creadas exitosamente:');
        foreach ($especies as $especie) {
            $this->command->info("   {$especie['icono']} {$especie['nombre']} ({$especie['nombre_cientifico']})");
        }
    }
}