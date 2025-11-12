<?php
require_once 'BaseModel.php';

class Role extends BaseModel
{
    public function getUsersCountByRole(): array
    {
        try {
            $sql = "SELECT r.role_name AS role_name, COUNT(u.id) AS user_count
                FROM roles r
                LEFT JOIN users u ON u.role_id = r.id
                GROUP BY r.role_name";

            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError("Role::getUsersCountByRole failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }
}
