<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $employees = [
            [1,'28770','CSC-2024-4524','MARYEL',null,'POSADA',null,'2025-07-28','active',null,1,3,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [2,'28851','CSC-2024-4605','MARCELINA','LATAM','POLINARIO',null,'2016-07-17','active',null,1,4,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [3,'28826','CSC-2024-4580','SARAH JOY','VELEZ','PAJE',null,'2018-04-25','active',null,1,5,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [4,'28673','CSC-2024-4427','RAMIL','BANTAS','PANAGAS',null,'2019-10-07','active',null,1,6,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [5,'30684','CSC-2025-0518','RIZA MAE','FABILA','PEOLIO',null,'2019-09-17','active',null,1,7,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [6,'29318','CSC-2024-5072','JULIUS JR.','ESTRIBA','PIANG',null,'2017-08-19','active',null,1,8,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [7,'30193','CSC-2025-0027','USAMA','MANABILANG','PALAWAN',null,'2018-08-01','active',null,1,9,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [8,'30460','CSC-2025-0294','SUNSHINE',null,'PAIDAN',null,'2023-02-10','active',null,1,10,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [9,'30587','CSC-2025-0421','ERNESTO','UMPA','PINOTE',null,'2017-03-31','active',null,1,11,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [10,'30581','CSC-2025-0415','JOHNNY','PAGATPAT','PARPADO',null,'2017-06-11','active',null,1,12,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [11,'30636','CSC-2025-0470','TAYA','EBAD','PAGAGAO',null,'2018-03-10','active',null,1,13,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [12,'30389','CSC-2025-0223','BEA','ALMODIVAR','PALACIO',null,'2025-01-09','active',null,1,14,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [13,'11220','CSC-2020-0222','MICHAEL ANGELO','FONTANOSA','QUIMQUE',null,'2016-03-18','active',null,1,15,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [14,'22813','CSC-2023-0505','JAYSON','CARAAN','QUITON',null,'2023-06-18','active',null,1,16,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [15,'29835','CSC-2024-5589','JAME PAUL','CADIO','QUIDIT',null,'2016-11-08','active',null,1,17,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [16,'787','CSC-2016-0581','MAHARLIKO NIKKO','BASILAN','QUILARIO',null,'2021-09-01','active',null,1,18,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [17,'7475','CSC-2019-1070','BEVERLY ANN','ARAGONCILLO','QUIJANO',null,'2021-07-08','active',null,1,19,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [18,'3028','CSC-2017-1851','KRISTINE','CANILLO','QUIMADA',null,'2021-02-26','active',null,1,20,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [19,'21172','CSC-2022-1770','GINA',null,'QUINOVIVA',null,'2020-05-05','active',null,1,21,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [20,'23239','CSC-2023-0926','DONDY',null,'QUINA',null,'2019-12-05','active',null,1,22,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [21,'23264','CSC-2023-0951','DELFIN JR.','AMARO','QUINA',null,'2019-12-11','active',null,1,23,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [22,'19790','CSC-2022-0393','ANATALIO','GOTICO','PANIS','JR.','2015-12-25','active',null,1,24,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [23,'28191','CSC-2024-3945','JENNYROSE','CABO','PAYAT',null,'2023-05-16','active',null,1,25,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [24,'28763','CSC-2024-4517','ANDRESON','PADIOS','PAJARI-ON',null,'2025-03-22','active',null,1,26,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [25,'27541','CSC-2024-3295','SARIPUDDIN','CAB','PIANG',null,'2019-09-10','active',null,1,27,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [26,'28877','CSC-2024-4631','THERESSA MAE C.','(PART-TIME)','PALMA',null,'2023-06-25','active',null,1,28,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [27,'27125','CSC-2024-2879','NORMA','KAMSA','PASAWILAN',null,'2016-10-16','active',null,1,29,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [28,'28767','CSC-2024-4521','JUN KEVIN','BOLONGON','PALAHANG',null,'2022-05-05','active',null,1,30,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [29,'29871','CSC-2024-5625','RAINISA','GUBAT','PALMAN',null,'2022-12-19','active',null,1,31,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [30,'26887','CSC-2024-2641','RAFFY','USOP','PANIOROTAN',null,'2018-10-16','active',null,1,32,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [31,'27903','CSC-2024-3657','DENMARK JAY','TANGGE','PALAWAN',null,'2021-05-10','active',null,1,33,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [32,'28677','CSC-2024-4431','NISAN','EBUS','PANAMBAY',null,'2024-01-17','active',null,1,34,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [33,'28845','CSC-2024-4599','SHINNY GRACE','OMANDAM','PESTAÑO',null,'2020-06-25','active',null,1,35,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [34,'29002','CSC-2024-4756','ALBASER','TANTONG','PEDRO',null,'2016-09-07','active',null,1,36,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [35,'28332','CSC-2024-4086','MARIBETH','PELONITA','PECHON',null,'2025-06-11','active',null,1,37,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [36,'28657','CSC-2024-4411','RYAN','IBUS','PANAMBAY',null,'2020-12-26','active',null,1,38,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [37,'29873','CSC-2024-5627','IMRAN','GUBAT','PALMAN',null,'2024-06-27','active',null,1,39,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [38,'23175','CSC-2023-0862','KATE INOKOW','JUAB','PALMA',null,'2019-03-18','active',null,1,40,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [39,'26406','CSC-2024-2160','MARVIN','OTIC','PLEÑOS',null,'2025-10-18','active',null,1,41,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [40,'12453','CSC-2020-1444','ANSARI','SAKILAN','PASAWERAN',null,'2023-01-20','active',null,1,42,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [41,'28066','CSC-2024-3820','CYRIL','FUENTES','PENAGUNDA',null,'2015-05-02','active',null,1,43,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [42,'28068','CSC-2024-3822','QUEGAY','PLANIA','PLANCO',null,'2018-01-01','active',null,1,44,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [43,'1185','CSC-2016-0979','MANUEL','MANLUNAS','PAUSAL','JR.','2025-05-26','active',null,1,45,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [44,'33963','CSC-2025-3797','JENNIFER','KASIM','PINDAILA',null,'2023-10-08','active',null,1,46,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [45,'33965','CSC-2025-3799','ALEXANDER',null,'PACE',null,'2024-05-03','active',null,1,47,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [46,'34028','CSC-2025-3853','JIMMY','PONSARAN','PEREZ',null,'2015-10-15','active',null,1,48,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [47,'11206','CSC-2020-0208','MARIE JOY','DAYON','PANTAJO',null,'2020-06-16','active',null,1,49,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [48,'34054','CSC-2025-3879','LOUIE JHON','VILLANUEVA','PORRAS',null,'2020-04-13','active',null,1,50,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [49,'29154','CSC-2024-4908','NOEL JR.','ADHAY','AGULONG',null,'2022-10-10','active',null,1,51,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
            [50,'33912','CSC-2025-3746','LUZ MARIE','SALADAGA','PARADERO',null,'2016-01-20','active',null,1,52,'2026-03-16 08:33:00','2026-03-16 08:33:00',null,1],
        ];

        foreach ($employees as $data) {
            DB::table('employees')->insert([
                'id' => $data[0],
                'system_id' => $data[1],
                'barcode_id' => $data[2],
                'first_name' => $data[3],
                'middle_name' => $data[4],
                'last_name' => $data[5],
                'suffix' => $data[6],
                'date_hired' => $data[7],
                'status' => $data[8],
                'archive_date' => $data[9],
                'company_id' => $data[10],
                'folder_id' => $data[11],
                'created_at' => $data[12],
                'updated_at' => $data[13],
                'deleted_at' => $data[14],
                'folder_location_id' => $data[15],
            ]);
        }
    }
}
