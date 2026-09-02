<?php
function get_active_period_id($conn)
{
    if (!$conn)
        return null;
    $sql = "SELECT id FROM periods WHERE status='active' LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    return null;
}

function get_active_period_details($conn)
{
    if (!$conn)
        return null;
    $sql = "SELECT * FROM periods WHERE status='active' LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}
?>