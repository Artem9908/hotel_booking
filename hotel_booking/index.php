<?php
require 'config.php';
include 'templates/header.php';

$startDate = new DateTime();
$endDate = clone $startDate;
$endDate->modify('+7 day');

$dates = [];
$period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
foreach ($period as $date) {
    $dates[] = $date->format('Y-m-d');
}

$stmt = $pdo->query('SELECT Rooms.RoomID, Rooms.RoomNumber, Buildings.BuildingName FROM Rooms JOIN Buildings ON Rooms.BuildingID = Buildings.BuildingID ORDER BY Buildings.BuildingName, Rooms.RoomNumber');
$rooms = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM Bookings WHERE CheckInDate <= ? AND CheckOutDate >= ?');
$stmt->execute([$endDate->format('Y-m-d'), $startDate->format('Y-m-d')]);
$bookings = $stmt->fetchAll();

$bookingsByRoom = [];
foreach ($bookings as $booking) {
    $bookingsByRoom[$booking['RoomID']][] = $booking;
}

echo '<table border="1">';
echo '<tr><th>Корпус/Номер</th>';
foreach ($dates as $date) {
    echo '<th>' . date('d.m', strtotime($date)) . '</th>';
}
echo '</tr>';

$currentBuilding = '';
foreach ($rooms as $room) {
    if ($currentBuilding !== $room['BuildingName']) {
        $currentBuilding = $room['BuildingName'];
        echo '<tr><td colspan="' . (count($dates) + 1) . '"><strong>Корпус ' . $currentBuilding . '</strong></td></tr>';
    }
    echo '<tr>';
    echo '<td>' . $room['RoomNumber'] . '</td>';
    foreach ($dates as $date) {
        $isBooked = false;
        if (isset($bookingsByRoom[$room['RoomID']])) {
            foreach ($bookingsByRoom[$room['RoomID']] as $booking) {
                if ($date >= $booking['CheckInDate'] && $date < $booking['CheckOutDate']) {
                    $isBooked = true;
                    break;
                }
            }
        }
        if ($isBooked) {
            echo '<td style="background-color: red;"></td>';
        } else {
            echo '<td><a href="booking_form.php?room_id=' . $room['RoomID'] . '&date=' . $date . '">Свободно</a></td>';
        }
    }
    echo '</tr>';
}
echo '</table>';

include 'templates/footer.php';
?>
