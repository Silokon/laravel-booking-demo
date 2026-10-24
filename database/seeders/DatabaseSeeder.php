<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Администратор', 'password' => bcrypt('password')],
        );

        $services = collect([
            ['Женская стрижка', 'Стрижка, мытьё и укладка', 60, 2500],
            ['Мужская стрижка', 'Классическая или модельная', 45, 1500],
            ['Окрашивание в один тон', 'С учётом длины до плеч', 120, 4800],
            ['Маникюр с покрытием', 'Аппаратный маникюр и гель-лак', 90, 2200],
            ['Педикюр', 'Аппаратный педикюр без покрытия', 60, 2400],
            ['Укладка', 'Укладка на брашинг или плойку', 45, 1800],
        ])->map(fn ($s) => Service::updateOrCreate(['name' => $s[0]], [
            'description' => $s[1], 'duration_minutes' => $s[2], 'price' => $s[3],
        ]));

        $masters = [
            ['Анна Соколова', 'Стилист-парикмахер', [0, 2, 5]],
            ['Игорь Белов', 'Барбер', [1, 5]],
            ['Мария Ким', 'Колорист', [0, 2, 5]],
            ['Елена Орлова', 'Мастер ногтевого сервиса', [3, 4]],
        ];
        foreach ($masters as [$name, $specialty, $serviceIdx]) {
            $master = Master::updateOrCreate(['name' => $name], ['specialty' => $specialty]);
            $master->services()->sync(collect($serviceIdx)->map(fn ($i) => $services[$i]->id));
        }

        // Несколько записей на ближайшие дни, чтобы в админке и в слотах было видно занятость.
        $tomorrow = CarbonImmutable::tomorrow();
        $today = CarbonImmutable::today();
        $monthAgo = fn (int $days, int $h) => CarbonImmutable::today()->subDays($days)->setTime($h, 0);
        $demo = [
            ['Анна Соколова', 0, $today->setTime(18, 0), 'Вера', 'confirmed'],
            ['Игорь Белов', 1, $today->setTime(19, 0), 'Максим', 'new'],
            ['Анна Соколова', 0, $monthAgo(1, 12), 'Алина', 'done'],
            ['Мария Ким', 2, $monthAgo(2, 15), 'Юлия', 'done'],
            ['Елена Орлова', 3, $monthAgo(3, 11), 'Светлана', 'done'],
            ['Игорь Белов', 1, $monthAgo(2, 17), 'Павел', 'cancelled'],
            ['Анна Соколова', 0, $tomorrow->setTime(11, 0), 'Ольга', 'confirmed'],
            ['Анна Соколова', 5, $tomorrow->setTime(14, 30), 'Дарья', 'new'],
            ['Игорь Белов', 1, $tomorrow->setTime(12, 0), 'Сергей', 'new'],
            ['Мария Ким', 2, $tomorrow->addDay()->setTime(10, 0), 'Наталья', 'confirmed'],
            ['Елена Орлова', 3, $tomorrow->setTime(16, 0), 'Ирина', 'new'],
        ];
        foreach ($demo as [$masterName, $serviceIdx, $start, $client, $status]) {
            $service = $services[$serviceIdx];
            Appointment::updateOrCreate(
                ['master_id' => Master::where('name', $masterName)->value('id'), 'starts_at' => $start],
                [
                    'service_id' => $service->id,
                    'ends_at' => $start->addMinutes($service->duration_minutes),
                    'client_name' => $client,
                    'client_phone' => '+7900'.random_int(1000000, 9999999),
                    'status' => $status,
                ],
            );
        }
    }
}
