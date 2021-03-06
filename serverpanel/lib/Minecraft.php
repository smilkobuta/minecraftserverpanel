<?php
require_once 'lib/Util.php';

class Minecraft {
    private static $screen_user = 'smilkobuta';
    public static $server_base_dir = null; // .env -> server_base_dir
    private static $servers_json = __DIR__ . '/../servers.json';

    /**
     * サーバー情報取得
     */
    public static function get_servers($options = []) {
        $servers = @json_decode(file_get_contents(self::$servers_json));
        if (!$servers) {
            $servers = [];
        }
        if (count($servers) > 0) {
            usort($servers, function($a, $b){
                if ($a->server_port == $b->server_port) {
                    return $a->server_name < $b->server_name ? -1 : 1;
                }
                return $a->server_port < $b->server_port ? -1 : 1;
            });

            if (!isset($options['status']) || $options['status'] != '0') {
                $statuses = self::get_server_statuses();
                foreach ($servers as $k => $v) {
                    if ($statuses[$v->server_id] ?? null) {
                        $servers[$k] = (object) array_merge((array) $v, (array) $statuses[$v->server_id]);
                    }
                }
            }

            foreach ($servers as $k => $v) {
                $servers[$k]->is_active = $v->is_active ?? null;
            }
        }

        return $servers;
    }

    /**
     * サーバー情報一件取得
     */
    public static function get_server($server_id, $options = []) {
        $servers = self::get_servers($options);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server_id) {
                return $v;
            }
        }
        return false;
    }

    /**
     * サーバー追加
     */
    public static function add_server($server) {
        self::check_server($server, true);

        $servers = self::get_servers([ 'status' => 0 ]);
        $servers[] = $server;
        $ret = file_put_contents(self::$servers_json, json_encode($servers));

        // ファイルのコピー
        exec('sudo -u ' . self::$screen_user . ' cp -Rpd ' . self::$server_base_dir . '/server-bedrock-template ' . self::$server_base_dir . '/server-bedrock-' . $server->server_id, $outputs, $retval);

        // server.propertiesを更新
        self::update_server_properties($server->server_id, 'level-name', $server->server_id);
        self::update_server_properties($server->server_id, 'server-name', $server->server_name);
        self::update_server_properties($server->server_id, 'server-port', $server->server_port);
        self::update_server_properties($server->server_id, 'server-portv6', $server->server_port_ipv6);
        if ($server->server_seed) {
            self::update_server_properties($server->server_id, 'level-seed', $server->server_seed);
        }

        return $ret;
    }

    /**
     * サーバー更新
     */
    public static function update_server($server) {
        self::check_server($server);

        $servers = self::get_servers([ 'status' => 0 ]);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server->server_id) {
                $servers[$k] = (object) array_merge((array) $v, (array) $server);
            }
        }
        $ret = file_put_contents(self::$servers_json, json_encode($servers));

        // server.propertiesを更新
        self::update_server_properties($server->server_id, 'server-name', $server->server_name);
        self::update_server_properties($server->server_id, 'server-port', $server->server_port);
        self::update_server_properties($server->server_id, 'server-portv6', $server->server_port_ipv6);
        self::update_server_properties($server->server_id, 'gamemode', $server->gamemode);
        self::update_server_properties($server->server_id, 'difficulty', $server->difficulty);
        self::update_server_properties($server->server_id, 'force-gamemode', 'true');
        self::update_server_properties($server->server_id, 'allow-cheats', $server->allow_cheats ? 'true' : 'false');

        return $ret;
    }

    /**
     * サーバー削除
     */
    public static function delete_server($server_id) {
        $servers = self::get_servers([ 'status' => 0 ]);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server_id) {
                unset($servers[$k]);
            }
        }
        $ret = file_put_contents(self::$servers_json, json_encode($servers));
        
        // ファイルの削除
        exec('sudo -u ' . self::$screen_user . ' rm -rf ' . self::$server_base_dir . '/server-bedrock-' . $server_id, $outputs, $retval);

        return $ret;
    }

    /**
     * サーバー情報チェック
     */
    public static function check_server($server, $is_new = false) {
        $errors = [];
        if ($server->server_id && preg_match('/[^a-z]/', $server->server_id)) {
            $errors[] = 'サーバーIDはアルファベットのみ入力してください。';
        }
        if ($is_new) {
            // 新規作成
            if (!$server->server_id) {
                $errors[] = 'サーバーIDを入力してください。';
            } else if (file_exists(self::$server_base_dir . '/server-bedrock-' . $server->server_id)) {
                $errors[] = 'このサーバーIDはすでに存在します';
            }
        }
        if (!$server->server_name) {
            $errors[] = 'サーバー名を入力してください。';
        }
        if (count($errors) > 0) {
            throw new Exception("入力内容に誤りがあります。\n" . implode("\n", $errors));
        }
    }
    
    /**
     * サーバーステータスを取得
     */
    public static function get_server_statuses() {
        exec('sudo -u ' . self::$screen_user . ' screen -ls', $outputs, $retval);
        $server_statuses = [];
        if ($retval == 0 && count($outputs) > 0) {
            foreach ($outputs as $line) {
                if (preg_match('/\s+([^\s]+)server-bedrock-([^\s]+).*(Detached|Attached).*/', $line, $matches)) {
                    $server_statuses[$matches[2]] = (object) [
                        'is_active' => true
                    ];
                }
            }
        }
        return $server_statuses;
    }

    /**
     * サーバーを起動
     */
    public static function start_server($server_id) {
        // 最大起動数をチェック
        $server = null;
        $servers = self::get_servers();
        $active_servers = [];
        foreach ($servers as $v) {
            if ($v->is_active) {
                $active_servers[$v->server_id] = $v;
            }
            if ($server_id == $v->server_id) {
                $server = $v;
            }
        }
        if (count($active_servers) >= 4) {
            throw new Exception("最大4つまで同期起動可能です。");
        }
        if (!$server) {
            throw new Exception("該当するサーバーが見つかりません。");
        }
        foreach ($active_servers as $v) {
            if ($v->server_port == $server->server_port) {
                throw new Exception("同じポート番号（IPv4）がすでに使われています。");
            } else if ($v->server_port_ipv6 == $server->server_port_ipv6) {
                throw new Exception("同じポート番号（IPv6）がすでに使われています。");
            }
        }

        list($retval, $outputs) = self::exec_command($server_id, 'start.sh');
        sleep(1);

        // コマンド実行
        self::exec_command($server->server_id, 'command.sh', '"gamerule showcoordinates ' . ($server->com_gamerule_showcoordinates ? 'true' : 'false') . '"');

        if ($retval == 0) {
            return true;
        }
        
        throw new Exception("起動処理に失敗しました。:" . implode("\n", $outputs));
    }

    /**
     * サーバーを停止
     */
    public static function stop_server($server_id) {
        list($retval, $outputs) = self::exec_command($server_id, 'stop.sh');
        sleep(1);
        if ($retval == 0 || count($outputs) == 0) {
            return true;
        }
        throw new Exception("停止処理に失敗しました。:" . implode("\n", $outputs));
    }

    /**
     * サーバーの設定ファイルを更新
     */
    public static function update_server_properties($server_id, $key, $value) {
        exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/update-server-properties.sh server-bedrock-' . $server_id . ' ' . $key . ' ' . $value, $outputs, $retval);
        if ($retval == 0) {
            return true;
        }
        throw new Exception("サーバーの設定ファイルの書き換えに失敗しました。:" . implode("\n", $outputs));
    }

    public static function get_new_server_ports() {
        $servers = self::get_servers([ 'status' => 0 ]);
        $new_ports = [
            'server_port' => 19130,
            'server_port_ipv6' => 19131,
        ];
        foreach ($servers as $v) {
            $new_ports['server_port'] = max($new_ports['server_port'], $v->server_port);
            $new_ports['server_port_ipv6'] = max($new_ports['server_port_ipv6'], $v->server_port_ipv6);
        }
        $new_ports['server_port'] += 2;
        $new_ports['server_port_ipv6'] += 2;
        return $new_ports;
    }

    public static function exec_command($server_id, $script_name, $arguments = '') {
        exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/server-bedrock-' . $server_id . '/' . $script_name . ' ' . $arguments, $outputs, $retval);
        return array($retval, $outputs);
    }

    public static function decorate_server_name($server_name) {
        $server_name = htmlspecialchars($server_name);

        // 色替え
        $server_name = preg_replace([
            '/§0/',
            '/§1/',
            '/§2/',
            '/§3/',
            '/§4/',
            '/§5/',
            '/§6/',
            '/§7/',
            '/§8/',
            '/§9/',
            '/§a/',
            '/§b/',
            '/§c/',
            '/§d/',
            '/§e/',
            '/§f/',
            '/§l/',
            '/§o/',
            '/§m/',
            '/§n/',
            '/§k/',
        ], [
            '</span><span class="c-1">',
            '</span><span class="c-2">',
            '</span><span class="c-3">',
            '</span><span class="c-4">',
            '</span><span class="c-5">',
            '</span><span class="c-6">',
            '</span><span class="c-7">',
            '</span><span class="c-8">',
            '</span><span class="c-9">',
            '</span><span class="c-10">',
            '</span><span class="c-11">',
            '</span><span class="c-12">',
            '</span><span class="c-13">',
            '</span><span class="c-14">',
            '</span><span class="c-15">',
            '</span><span class="c-16">',
            '<span style="font-weight:900;">',
            '<span style="font-style:italic;">',
            '<span style="text-decoration:line-through;">',
            '<span style="text-decoration:underline;">',
            '<span class="obfuscated">',
        ], $server_name);

        $prev_reset_pos = 0;
        while (strpos($server_name, '§r') !== false) {
            $reset_pos = strpos($server_name, '§r');
            $part1 = substr($server_name, $prev_reset_pos, $reset_pos);
            $part2 = substr($server_name, $reset_pos + strlen('§r'));
            $reset_code = str_repeat('</span>', substr_count($part1, '<span'));
            $server_name = $part1 . $reset_code . $part2;
        }

        return $server_name;
    }
}
Minecraft::$server_base_dir = get_env('server_base_dir');

class Server {
    public $server_id;
    public $server_name;
    public $server_seed;
    public $server_port;
    public $server_port_ipv6;
    public $is_active;
}
?>