<?php
require 'config.php';
include 'templates/header.php';

$room_id = $_GET['room_id'] ?? null;
$date = $_GET['date'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $total_cost = $_POST['total_cost'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO Clients (FirstName, LastName, Phone, Email) VALUES (?, ?, ?, ?)');
        $stmt->execute([$first_name, $last_name, $phone, $email]);
        $client_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO Bookings (RoomID, ClientID, CheckInDate, CheckOutDate, TotalCost) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$room_id, $client_id, $check_in, $check_out, $total_cost]);

        $pdo->commit();
        echo '<p>Бронирование успешно создано!</p>';
    } catch (Exception $e) {
        $pdo->rollBack();
        echo '<p>Ошибка: ' . $e->getMessage() . '</p>';
    }
} else {
    ?>
    <form method="post" action="booking_form.php">
        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
        <label>Дата заезда: <input type="date" name="check_in" value="<?php echo htmlspecialchars($date); ?>" required></label><br>
        <label>Дата выезда: <input type="date" name="check_out" required></label><br>
        <h3>Данные клиента:</h3>
        <label>Имя: <input type="text" name="first_name" required></label><br>
        <label>Фамилия: <input type="text" name="last_name" required></label><br>
        <label>Телефон: <input type="text" name="phone"></label><br>
        <label>Email: <input type="email" name="email"></label><br>
        <label>Сумма к оплате: <input type="number" name="total_cost" required></label><br>
        <button type="submit">ОК</button>
        <button type="button" onclick="window.location.href='index.php'">Отмена</button>
    </form>
    <?php
}

include 'templates/footer.php';
?>

