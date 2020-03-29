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

    $gamemode = $server->gamemode ?? 'survival';
    $gamemode = $_POST['gamemode'] ?? $gamemode;

    $difficulty = $server->difficulty ?? 'normal';
    $difficulty = $_POST['difficulty'] ?? $difficulty;
    
    $com_gamerule_showcoordinates = $server->com_gamerule_showcoordinates ?? false;
    $com_gamerule_showcoordinates = $_POST['com_gamerule_showcoordinates'] ?? $com_gamerule_showcoordinates;

    $allow_cheats = $server->allow_cheats ?? false;
    $allow_cheats = $_POST['allow_cheats'] ?? $allow_cheats;

    if ($server_edit) {
        $server->server_name = $server_name;
        $server->server_port = $server_port;
        $server->server_port_ipv6 = $server_port_ipv6;
        $server->gamemode = $gamemode;
        $server->difficulty = $difficulty;
        $server->com_gamerule_showcoordinates = $com_gamerule_showcoordinates;
        $server->allow_cheats = $allow_cheats;

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
                        <div class="form-group has-success">
                            <label class="control-label mb-1">ゲームモード</label>
                            <br>
                            <select name="gamemode" class="form-control">
                                <option value="survival" <?php if ($gamemode == 'survival') { ?>selected<?php } ?>>サバイバル</option>
                                <option value="creative" <?php if ($gamemode == 'creative') { ?>selected<?php } ?>>クリエイティブ</option>
                                <option value="adventure" <?php if ($gamemode == 'adventure') { ?>selected<?php } ?>>アドベンチャー</option>
                            </select>
                        </div>
                        <div class="form-group has-success">
                            <label class="control-label mb-1">難易度</label>
                            <br>
                            <select name="difficulty" class="form-control">
                                <option value="peaceful" <?php if ($difficulty == 'peaceful') { ?>selected<?php } ?>>ピースフル</option>
                                <option value="easy" <?php if ($difficulty == 'easy') { ?>selected<?php } ?>>イージー</option>
                                <option value="normal" <?php if ($difficulty == 'normal') { ?>selected<?php } ?>>ノーマル</option>
                                <option value="hard" <?php if ($difficulty == 'hard') { ?>selected<?php } ?>>ハード</option>
                            </select>
                        </div>
                        <div class="form-group has-success">
                            <label class="control-label mb-1">その他設定</label>
                            <br>
                            <label for="com_gamerule_showcoordinates" style="font-weight:normal;">
                                <input name="com_gamerule_showcoordinates" type="hidden" value="0">
                                <input id="com_gamerule_showcoordinates" name="com_gamerule_showcoordinates" type="checkbox" value="1" <?php if ($com_gamerule_showcoordinates) { ?>checked<?php } ?>>
                                座標を表示する
                            </label>
                            <br>
                            <label for="allow_cheats" style="font-weight:normal;">
                                <input name="allow_cheats" type="hidden" value="0">
                                <input id="allow_cheats" name="allow_cheats" type="checkbox" value="1" <?php if ($allow_cheats) { ?>checked<?php } ?>>
                                チートの許可
                            </label>
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
