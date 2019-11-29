<?php
    require_once 'lib/User.php';

    $login = isset($_POST['login']) ? true : false;
    $logout = isset($_GET['logout']) ? true : false;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $remember = $_POST['remember'] ?? null;

    if ($login) {
        if (User::login($email, $password, $remember)) {
            // ログイン成功
            header('Location: ./');
            exit;
        } else {
            //ログイン失敗
            $error_message = 'メールアドレスかパスワードが間違っています。';
        }
    } else if ($logout) {
        // ログアウト
        User::logout();
        $message = 'ログアウトしました。';
    } else if (User::cookie_login()) {
        header('Location: ./');
        exit;
    }
?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html lang="ja">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sufee Admin - HTML5 Admin Template</title>
    <meta name="description" content="Sufee Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">
    <link rel="shortcut icon" href="favicon.ico">


    <link rel="stylesheet" href="vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="vendors/themify-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/selectFX/css/cs-skin-elastic.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>



</head>

<body class="bg-dark">


    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo">
                    <a href="index.php">
                        <img class="align-content" src="images/logo.png" alt="">
                    </a>
                </div>
                <div class="login-form">
                    <?php if ($message ?? null) { ?>
                        <div class="alert alert-success">
                            <?php echo $message ?>
                        </div>
                    <?php } ?>
                    <?php if ($error_message ?? null) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message ?>
                        </div>
                    <?php } ?>
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label>メールアドレス</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="メールアドレスを入力してください" required>
                        </div>
                        <div class="form-group">
                            <label>パスワード</label>
                            <input type="password" name="password" class="form-control" placeholder="パスワードを入力してください" required>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="remember" checked> ログインを記憶
                            </label>
                        </div>
                        <button type="submit" name="login" class="btn btn-success btn-flat m-b-30 m-t-30">ログイン</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <script src="vendors/popper.js/dist/umd/popper.min.js"></script>
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>


</body>

</html>
