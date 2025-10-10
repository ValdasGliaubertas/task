<?php

header('Content-Type: application/json');
echo json_encode($response ?? ['status' => 'error', 'errors' => $errors ?? []]);