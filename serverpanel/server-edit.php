<?php
    require_once 'lib/User.php';
    require_once 'lib/Util.php';
    require_once 'lib/Minecraft.php';

    $user = User::auth();
    
    $server_id = $_POST['server_id'] ?? null;
    if ($_GET['server_id'] ?? null) {
        $server_id = $_GET['server_id'];
    }

    $server = Minecraft::get_server($server_id);
    if (!$server) {
        header('Location: ./');
        exit;
    }

    $server_edit = isset($_POST['server_edit']) ? true : false;
    $server_name = $_POST['server_name'] ?? $server->server_name;
    $server_port = $_POST['server_port'] ?? $server->server_port;
    $server_port_ipv6 = $_POST['server_port_ipv6'] ?? $server->server_port_ipv6;

    if ($server_edit) {
        $server->server_name = $server_name;
        $server->server_port = $server_port;
        $server->server_port_ipv6 = $server_port_ipv6;

        try {
            if (Minecraft::update_server($server)) {
                add_session_message('サーバーを更新しました。');
                header('Location: ./');
                exit;
            } else {
                $error_message = 'サーバーを更新できませんでした。';
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

    }
?>
<?php include_once 'header.php'; ?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>サーバー編集</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="./">サーバー一覧</a></li>
                    <li class="active">サーバー編集</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">サーバー情報</strong>
                </div>
                <div class="card-body">
                    <?php if ($error_message ?? null) { ?>
                        <div class="alert alert-danger">
                            <?php echo nl2br(h($error_message)) ?>
                        </div>
                    <?php } ?>
                    <form action="server-edit.php" method="post">
                        <input type="hidden" name="server_id" value="<?php echo h($server->server_id); ?>">
                        <div class="form-group">
                            <label for="server_id" class="control-label mb-1">サーバーID</label>
                            <span><?php echo h($server->server_id); ?></span>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_name" class="control-label mb-1">サーバー名</label>
                            <input id="server_name" name="server_name" type="text" class="form-control server_name" value="<?php echo h($server_name); ?>" placeholder="日本語、アルファベットで入力してください" required>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_port" class="control-label mb-1">ポート番号</label>
                            <br>
                            IPv4用: <input id="server_port" name="server_port" type="text" class="form-control server_port" value="<?php echo h($server_port); ?>" placeholder="IPv4用。数字で入力してください" required>
                            <br>
                            IPv6用: <input id="server_port_ipv6" name="server_port_ipv6" type="text" class="form-control server_port_ipv6" value="<?php echo h($server_port_ipv6); ?>" placeholder="IPv6用。数字で入力してください" required>
                        </div>
                        <div>
                            <button type="submit" name="server_edit" class="btn btn-lg btn-info btn-block">
                                <i class="fa fa-bolt fa-lg"></i>&nbsp;
                                <span>更新する</span>
                            </button>
                        </div>
                    </form>

                </div> <!-- .card -->
            </div>

        </div>
    </div><!-- .animated -->
</div><!-- .content -->

<?php include_once 'footer.php'; ?>
