<?php
$base = "C:\\Users\\owais\\Desktop\\mms-backend\\";
require $base."vendor/autoload.php";
$app = require $base."bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$data = [
  "welcome_message" => "x","date_formatted" => "x",
  "kpi_cards" => [
    "monthly_donations" => ["value"=>0,"formatted_value"=>"0","percentage_change"=>"0%","is_increase"=>false],
    "open_maintenance_requests" => ["value"=>0,"percentage_change"=>"0%","is_increase"=>false],
    "complaints" => ["value"=>0,"percentage_change"=>"0%","is_increase"=>false],
    "accredited_volunteers" => ["value"=>0,"percentage_change"=>"0%","is_increase"=>false],
  ],
  "recent_activities" => [],"latest_requests" => [],
];
$stats = ["role"=>"mosque_manager","total_students"=>0,"total_teachers"=>0,"total_volunteers"=>0,"pending_invitations"=>0,"donations"=>0.0,"open_maintenance_requests"=>0,"complaints"=>0,"accredited_volunteers"=>0];
try {
  $html = view("dashboard::reports.mosque_manager", ["data"=>$data,"stats"=>$stats,"mosque_name"=>"M","user_name"=>"U","user_role"=>"R","generated_at"=>"now","recommendations"=>[]])->render();
  echo "BLADE_OK length=".strlen($html)."\n";
} catch (\Throwable $e) {
  echo "BLADE_ERROR: ".get_class($e).": ".$e->getMessage()."\n";
  echo $e->getFile().":".$e->getLine()."\n";
}
