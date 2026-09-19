<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'vehicle' => ['Toyota', 'Honda', 'Mitsubishi', 'Ford', 'Hyundai', 'Nissan', 'Suzuki', 'Kia', 'Mazda', 'BMW', 'BYD', 'Geely', 'MG', 'Changan', 'Chery', 'GAC', 'Tesla', 'JMC'],
            'truck' => ['Isuzu', 'Mitsubishi', 'Hino', 'Foton', 'JMC', 'Volvo', 'Sinotruk', 'Shacman', 'Dongfeng', 'FAW', 'HOWO', 'JAC'],
            'motorcycle' => ['Yamaha', 'Honda', 'BMW', 'Kawasaki', 'KTM', 'Ducati', 'Kymco', 'SYM', 'Motorstar', 'Rusi', 'Suzuki'],
            'threeWheel' => ['TVS', 'Bajaj', 'Piaggio', 'Can-Am', 'HATASU-Ebikes', 'NWOW'],
            'eBike' => ['NWOW', 'Yadea', 'Nakto', 'ADO', 'Supremo', 'OEM'],
            'equipment' => ['Komatsu', 'Volvo', 'Hyundai', 'Caterpillar', 'Hitachi', 'SANY', 'SDLG', 'XCMG', 'Zoomlion', 'LiuGong', 'Develon', 'Lonking', 'Sinotruk', 'Hino', 'Daewoo', 'Sandvik'],
            'farm' => ['Kubota', 'Yanmar', 'John Deere', 'LOVOL', 'TYM', 'FitCorea', 'OEM', 'Mahindra'],
        ])->each(function (array $names, string $group): void {
            collect($names)->each(function (string $name, int $index) use ($group): void {
                Brand::query()->updateOrCreate(
                    ['name' => $name, 'category_group' => $group],
                    [
                        'logo' => '/brand-logos/'.str($name)->lower()->replace([' ', '-'], '')->toString().'.svg',
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            });
        });
    }
}
