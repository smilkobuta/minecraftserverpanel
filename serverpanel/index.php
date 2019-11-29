<?php
    require_once 'lib/User.php';
    require_once 'lib/Util.php';
    require_once 'lib/Minecraft.php';

    $user = User::auth();
    
    $servers = Minecraft::get_servers();
    $message = get_session_message();
    $error_message = get_session_error_message();

    $server_id = $_GET['server_id'] ?? null;
    if ($server_id) {
        $server = Minecraft::get_server($server_id);
        if (!$server) {
            header('Location: ./');
            exit;
        }
    }

    $server_start = isset($_GET['server_start']) ? true : false;
    $server_stop = isset($_GET['server_stop']) ? true : false;

    if ($server_start) {
        try {
            if (Minecraft::start_server($server_id)) {
                add_session_message('サーバーを開始しました。');
            } else {
                add_session_error_message('サーバーを開始できませんでした。');
            }
        } catch (Exception $e) {
            add_session_error_message($e->getMessage());
        }
        header('Location: ./');
        exit;
    } else if ($server_stop) {
        try {
            if (Minecraft::stop_server($server_id)) {
                add_session_message('サーバーを停止しました。');
            } else {
                add_session_error_message('サーバーを停止できませんでした。');
            }
        } catch (Exception $e) {
            add_session_error_message($e->getMessage());
        }
        header('Location: ./');
        exit;
    }
?>
<?php include_once 'header.php'; ?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>サーバー一覧</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <!--
                    <li class="active">サーバー一覧</li>
                    -->
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
                        <strong class="card-title">Minecraftサーバー</strong>
                    </div>
                    <div class="card-body">
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

                        <?php if (count($servers) > 0) { ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">サーバー名</th>
                                    <th scope="col">ポート番号</th>
                                    <th scope="col">稼働</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servers as $k => $server) { ?>
                                <tr>
                                    <th scope="row"><?php echo $k + 1; ?></th>
                                    <td><?php echo h($server->server_name) ?></td>
                                    <td><?php echo h($server->server_port) ?></td>
                                    <td>
                                        <?php if ($server->is_active) { ?>
                                            <a href="index.php?server_stop&server_id=<?php echo h($server->server_id); ?>" class="btn btn-sm btn-danger"><i class="fa fa-stop"></i>&nbsp; 停止する</a>
                                        <?php } else { ?>
                                            <a href="index.php?server_start&server_id=<?php echo h($server->server_id); ?>" class="btn btn-sm btn-info"><i class="fa fa-play"></i>&nbsp; 開始する</a>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($server->is_active) { ?>
                                            <span class="btn btn-primary btn-sm disabled" style="opacity:0.2;cursor:default;"><i class="fa fa-edit"></i>&nbsp; サーバー編集</span>
                                            <span class="btn btn-danger btn-sm disabled" style="opacity:0.2;cursor:default;"><i class="fa fa-trash-o"></i>&nbsp; サーバー削除</span>
                                        <?php } else { ?>
                                            <a href="server-edit.php?server_id=<?php echo h($server->server_id); ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i>&nbsp; サーバー編集</a>
                                            <a href="server-delete.php?server_id=<?php echo h($server->server_id); ?>" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i>&nbsp; サーバー削除</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                            </tbody>
                        </table>
                        <?php } ?>

                        <a href="server-add.php" class="btn btn-success"><i class="fa fa-plus"></i>&nbsp; サーバー追加</a>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div><!-- .content -->

<?php include_once 'footer.php'; ?>
