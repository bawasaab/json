include_once './DrFunction.php';
    $data = [];
    
    $data['host_name'] = 'localhost';
    $data['user_name'] = 'root';
    $data['password'] = '';
    $data['database_name'] = 'name';
    
    $obj = new Function($data);
