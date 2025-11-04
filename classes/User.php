<?php
require_once 'BaseModel.php';

class User extends BaseModel
{
    /**
     * Checks if the provided email is already in use.
     * @param string $email The email address to check.
     * @return bool True if the email is occupied, false otherwise.
     * @throws RuntimeException If the database query fails.
     */
    public function isEmailOccupied(string $email): bool
    {
        try {
            $conn = $this->getConn();
            $queryLookForEmail = "SELECT email FROM users WHERE email = :email";
            $query = $conn->prepare($queryLookForEmail);
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->execute();
            return $query->rowCount() >= 1;
        } catch (\PDOException $e) {
            logError("User::isEmailOccupied failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /** Inserts a new user into the database.
     * 
     * @param array $data An associative array containing user data with keys:
     *                    'email', 'password', 'name', 'surname', 'phone', and 'verification_code'.
     * @throws RuntimeException If the database insertion fails.
     * @see BaseModel::insertRow()
     */
    public function create(array $data): void
    {
        $this->insertRow("users", [
            "email"             => $data["email"],
            "password"          => $data["password"],
            "name"              => $data["name"],
            "surname"           => $data["surname"],
            "phone"             => $data["phone"],
            "role_id"           => 1, // default role is 'user'
            "department_id"     => null,
            "verification_code" => $data["verification_code"],
            "verified"          => 0 // default is unverified
        ]);
    }

    /**
     * Marks the user as verified by setting the verified field to 1 and clearing the verification code.
     * 
     * @param string $email The email of the user to update.
     * @return bool Returns true if the update was successful, false otherwise.
     * @throws RuntimeException If the database update fails.
     */
    public function makeUserVerified(string $email): bool
    {
        try {
            $query = "UPDATE users SET verification_code = null, verified = 1 WHERE email = :em";
            $stmt = $this->getConn()->prepare($query);
            $stmt->bindValue(':em', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0 ? true : false;
        } catch (\PDOException $e) {
            logError("User::makeUserVerified failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Fetches the verification code for a user by email.
     * 
     * @param string $email The email of the user.
     * @return string|null The verification code if found, null otherwise.
     * @throws RuntimeException If the database query fails.
     */
    public function gettingUserVerificationCode($email): string|null
    {
        try {
            $verificationCodeQuery = "SELECT verification_code FROM users WHERE email = :em";
            $query = $this->getConn()->prepare($verificationCodeQuery);
            $query->bindValue(':em', $email, PDO::PARAM_STR);
            $query->execute();
            return $query->rowCount() >= 1 ? $query->fetchColumn() : null;
        } catch (\PDOException $e) {
            logError("User::gettingUserVerificationCode failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Fetches all users from users table in the database.
     *
     * @param string|null $where Optional WHERE clause (e.g. "status = 'active' AND role_id = 2").
     * @param int|null $limit Optional limit for the number of returned rows.
     * @param int|null $offset Optional offset for the returned rows.
     * @return array An array of associative arrays, each representing a user.
     * @throws RuntimeException If the database query fails.
     */
    public function getAllUsers(?string $where = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getAllWhere("users", $where, $limit, $offset);
    }

    /**
     * Fetches row from user table by email.
     * @param string $email The email to search for.
     * @return array|false Associative array with user data if found, false otherwise.
     * @throws RuntimeException If the database query fails.
     */
    public function getUserByEmail(string $email): array|false
    {
        try {
            $getUserByEmailQuery = "SELECT 
                                    u.id AS u_id, 
                                    u.email AS u_email, 
                                    u.password AS u_password, 
                                    u.name AS u_name, 
                                    u.surname AS u_surname, 
                                    u.role_id  AS u_role_id, 
                                    u.phone AS u_phone,  
                                    u.department_id AS u_department_id, 
                                    u.verified AS u_verified, 
                                    u.session_version AS u_session_version, 
                                    d.id AS d_id, 
                                    d.name AS d_name, 
                                    r.id AS r_id, 
                                    r.role_name AS r_name
                                FROM users as u
                                LEFT JOIN departments as d ON u.department_id = d.id 
                                LEFT JOIN roles as r ON u.role_id = r.id 
                                WHERE email = :em";

            $query = $this->getConn()->prepare($getUserByEmailQuery);
            $query->bindValue(":em", $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return !empty($result) ? $result : false;
        } catch (\PDOException $e) {
            logError("User::getUserByEmail failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Fetches row from user table by row id.
     * 
     * @param int $id ID column from users table.
     * 
     * @return array|null Returns user row as associative array, or null if not found.
     * @throws RuntimeException If the database query fails.
     * @see BaseModel::getAllWhere()
     */
    public function getUserById(int $id): ?array
    {
        return $this->getAllWhere("users", "id = {$id}")[0] ?? null;
    }

    /**
     * Fetches all users from users table that match the given WHERE clause.
     *
     * @param string $where MySQL WHERE clause (e.g. "role_id = 1").
     * @return array An array of associative arrays, each representing a user.
     * @throws RuntimeException If the database query fails.
     * @see BaseModel::getAllWhere()
     */
    public function getAllResultsWhere(string $where): array
    {
        return $this->getAllWhere("users", $where);
    }

    /**
     * Retrieves the hashed password for a given email address.
     *
     * @param string $email The email address of the user.
     * @return string|null The hashed password if found, or null if not found.
     * @throws RuntimeException If the database query fails.
     */
    public function getPasswordByEmail($email): string|null
    {
        try {
            $getPasswordByEmail = "SELECT password FROM users WHERE email = :em";
            $query = $this->getConn()->prepare($getPasswordByEmail);
            $query->bindValue(":em", $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return !empty($result) ? $result['password'] : null;
        } catch (\PDOException $e) {
            logError("User::getPasswordByEmail failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Adds a verification code to the user identified by email.
     *
     * @param string $verificationCode The verification code to add.
     * @param string $email The email of the user to update.
     * @return bool Returns true if the update was successful, false otherwise.
     * @throws RuntimeException If the database update fails.
     */
    public function addVerificationCodeToUser(string $verificationCode, string $email): bool
    {
        try {
            $query = "UPDATE users SET verification_code = :vc WHERE email = :em";
            $stmt = $this->getConn()->prepare($query);
            $stmt->bindValue(':vc', $verificationCode, PDO::PARAM_STR);
            $stmt->bindValue(':em', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0 ? true : false;
        } catch (\PDOException $e) {
            logError("User::addVerificationCodeToUser failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Marks the user as unverified by setting the verified field to 0.
     *
     * @param string $email The email of the user to update.
     * @return bool Returns true if the update was successful, false otherwise.
     * @throws RuntimeException If the database update fails.
     */
    public function markAsUnverified(string $email): bool
    {
        try {
            $query = "UPDATE users SET verified = 0 WHERE email = :em";
            $stmt = $this->getConn()->prepare($query);
            $stmt->bindValue(':em', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0 ? true : false;
        } catch (\PDOException $e) {
            logError("User::markAsUnverified failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Updates user data in the database.
     * 
     * @param array $data An associative array of user data to update. Possible keys: 'email', 'name', 'surname', 'phone'.
     * @param int $profileId The ID of the user to update.
     * @return true Returns true if the update succeeds.
     * @throws RuntimeException If the database update fails.
     * @throws InvalidArgumentException If the email is already in use.
     * @see BaseModel::updateRows()
     */
    public function updateUserRow(array $data, int $profileId): bool
    {
        $where = ["id" => $profileId];

        if (!empty($data["email"])) {
            $theUser = $this->getUserById($profileId);
            if ($theUser["email"] === $data["email"]) {
                // If the same email is already set to the account, don't update the email 
                unset($data["email"]);
            } else {
                if ($this->isEmailOccupied($data["email"])) {
                    throw new InvalidArgumentException("Email is aready in use!");
                };
            }
        }

        $this->updateRows("users", [$data], [$where]);
        return true;
    }

    /**
     * Updates user password.
     *
     * @param int $id User id.
     * @param string $hashedPassword The new hashed password.
     * @return void
     * @throws RuntimeException If the database update fails.
     * @see BaseModel::updateRows()
     */
    public function updatePassword(int $id, string $hashedPassword): void
    {
        $data  = [["password" => $hashedPassword]];
        $where = [["id" => $id]];

        $this->updateRows('users', $data, $where);
    }

    /**
     * Retrieves all users along with their respective ticket counts.
     *
     * @param string|null $where Optional SQL WHERE clause to filter users (e.g. "role_id = 1").
     * @param int|null $limit Optional limit on the number of users to retrieve.
     * @param int|null $offset Optional offset for the returned users.
     * @param string $orderBy Order by direction, either "ASC" or "DESC".
     * @return array An array of users with an additional 'tickets_count' field.
     * @throws RuntimeException If the database query fails.
     */
    public function getAllUsersWithTicketsCount(?string $where = null, ?int $limit = null, ?int $offset = null, string $orderBy = "ASC"): array
    {
        try {
            $query = "SELECT u.*, COUNT(t.id) AS tickets_count 
                FROM users AS u
                LEFT JOIN tickets AS t ON u.id = t.created_by";

            if ($where !== null) {
                $query .= " WHERE {$where}";
            }

            $query .= " GROUP BY u.id
                ORDER BY u.id {$orderBy}";

            if ($limit !== 0) {
                $query .= " LIMIT :limit";
            }

            if ($offset !== 0) {
                $query .= " OFFSET :offset";
            }

            $stmt = $this->getConn()->prepare($query);
            if ($limit !== 0) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            if ($offset !== 0) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logError("User::getAllUsersWithTickets failed. ", ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            throw new RuntimeException("Request failed. Try again.");
        }
    }

    /**
     * Counts total users in the users table with optional WHERE clause.
     *
     * @param string|null $where Optional SQL WHERE clause to filter users.
     * @return int The total number of users.
     * @throws RuntimeException If the database query fails.
     * @see BaseModel::countRows()
     */
    public function countUsers(?string $where = null): int
    {
        return $this->countRows("users", $where);
    }
}
