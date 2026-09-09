<?php
require_once '../../functions/init.php';
require_auth('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
db_exec($conn, 'DELETE FROM users WHERE id_user = ?', 'i', [$id]);

header('Location: index.php');
exit;