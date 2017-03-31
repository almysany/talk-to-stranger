<?php

/**
 * Created by PhpStorm.
 * User: luqman
 * Date: 3/31/17
 * Time: 5:56 AM
 */
class User{

    const STATUS_IDLE = 0;
    const STATUS_LOOKING = 1;
    const STATUS_IN_CHAT = 2;

    public $id,
        $user_id,
        $display_name,
        $current_friend_id,
        $chat_quota,
        $status;

    private $db;

    public function __construct()
    {
        $this->db = DB::getDB();
    }

    public function insert(){
        $sql = "INSERT INTO users (user_id, display_name, current_friend_id, chat_quota, status) VALUES (:user_id, :display_name, '', 30, :status)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $this->user_id,
            'display_name' => $this->display_name,
            'status' => self::STATUS_IDLE,
        ]);
        $this->id = $this->db->lastInsertId();
        $this->chat_quota = 30;
    }

    public static function exist($user_id){
        $statement = DB::getDB()->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $statement->execute(['user_id' => $user_id]);

        return $statement->rowCount() > 0;
    }

    public function save(){
        $sql = "UPDATE users SET current_friend_id=:current_friend_id, chat_quota=:chat_quota, status=:status WHERE id={$this->id}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'current_friend_id' => $this->current_friend_id,
            'chat_quota' => $this->chat_quota,
            'status' => $this->status
        ]);
    }

    public function getChatMate(){
        $query = "SELECT * FROM users WHERE status = ".self::STATUS_LOOKING;

        $users = DB::getDB()->query($query)->fetchAll(PDO::FETCH_ASSOC);

        $jumlah = count($users);

        if($jumlah < 1){
            return false;
        }else{
            $user = $users[mt_rand(0, $jumlah - 1)];
            $mate = User::findOne(['id' => $user['id']]);
            $mate->chat_quota = 30;
            $mate->status = self::STATUS_IN_CHAT;
            $mate->current_friend_id = $this->user_id;
            $mate->save();
            BotHelper::greetMate($mate->user_id);

            $this->chat_quota = 30;
            $this->status = self::STATUS_IN_CHAT;
            $this->current_friend_id = $mate->user_id;
            return true;

        }
    }

    /**
     * @param $params
     * @return User
     */
    public static function findOne($params){
        $sql = "SELECT * FROM users";
        if(! empty($params)){
            $sql .= " WHERE";
            foreach (array_keys($params) as $key) {
                $sql .= " {$key} = :{$key} AND";
            }
            $sql = substr($sql, 0, -3);
        }
        $stmt = DB::getDB()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchObject('User');

    }
}