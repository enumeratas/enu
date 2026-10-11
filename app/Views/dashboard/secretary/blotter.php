<?php
$role = $role ?? (session()->get('role') ?? 'secretary');
include(APPPATH . 'Views/dashboard/captain/blotter.php');
