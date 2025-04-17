<?php
class Student
{
    private $conn;
    private $table_name = "students";

    public $id;
    public $name;
    public $age;
    public $gender;
    public $academic_performance;
    public $attendance;
    public $socio_economic_status;
    public $family_support;
    public $dropout_risk;
    public $intervention_status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    age = :age,
                    gender = :gender,
                    academic_performance = :academic_performance,
                    attendance = :attendance,
                    socio_economic_status = :socio_economic_status,
                    family_support = :family_support,
                    dropout_risk = :dropout_risk,
                    intervention_status = 'Pending'";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->academic_performance = htmlspecialchars(strip_tags($this->academic_performance));
        $this->attendance = htmlspecialchars(strip_tags($this->attendance));
        $this->socio_economic_status = htmlspecialchars(strip_tags($this->socio_economic_status));
        $this->family_support = htmlspecialchars(strip_tags($this->family_support));
        $this->dropout_risk = $this->calculateDropoutRisk();

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":academic_performance", $this->academic_performance);
        $stmt->bindParam(":attendance", $this->attendance);
        $stmt->bindParam(":socio_economic_status", $this->socio_economic_status);
        $stmt->bindParam(":family_support", $this->family_support);
        $stmt->bindParam(":dropout_risk", $this->dropout_risk);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    private function calculateDropoutRisk()
    {
        // Simple risk calculation based on multiple factors
        $risk_score = 0;

        // Academic Performance (0-100)
        $risk_score += (100 - $this->academic_performance) * 0.3;

        // Attendance (0-100)
        $risk_score += (100 - $this->attendance) * 0.2;

        // Socio-economic status (1-5, 5 being worst)
        $risk_score += $this->socio_economic_status * 10;

        // Family support (1-5, 5 being worst)
        $risk_score += $this->family_support * 10;

        // Normalize to 0-100
        $risk_score = min(100, max(0, $risk_score));

        return $risk_score;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getHighRiskStudents()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE dropout_risk >= 70";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateInterventionStatus()
    {
        $query = "UPDATE " . $this->table_name . "
                SET intervention_status = :intervention_status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":intervention_status", $this->intervention_status);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
