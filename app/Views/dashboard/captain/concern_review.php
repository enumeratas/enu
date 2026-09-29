<?php
// Captain uses the same review layout as secretary.
echo view('dashboard/secretary/concern_review', ['concern' => $concern, 'role' => $role, 'pageTitle' => $pageTitle]);
