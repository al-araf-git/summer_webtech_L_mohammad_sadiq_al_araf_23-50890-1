<?php
$errors = [];
$values = [
    'first-name' => '',
    'last-name' => '',
    'email' => '',
    'phone-number' => '',
    'visit-date' => '',
    'total-traveler' => '0',
    'interests' => [],
    'contact-method' => [],
    'callback-time' => '',
    'extra-info' => '',
    'referral' => ''
];
$successMsg = '';
$totalPrice = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function clean_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $values['first-name'] = clean_input($_POST['first-name'] ?? '');
    if (empty($values['first-name'])) {
        $errors['first-name'] = "First name is required.";
    }
    $values['last-name'] = clean_input($_POST['last-name'] ?? '');
    if (empty($values['last-name'])) {
        $errors['last-name'] = "Last name is required.";
    }

    $values['email'] = clean_input($_POST['email'] ?? '');
    if (empty($values['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    $values['phone-number'] = clean_input($_POST['phone-number'] ?? '');
    if (!empty($values['phone-number'])) {
        if (!preg_match('/^(\+?88)?01[3-9]\d{8}$/', $values['phone-number'])) {
            $errors['phone-number'] = "Invalid Bangladesh phone number format.";
        }
    }

    $values['visit-date'] = $_POST['visit-date'] ?? '';
    if (empty($values['visit-date'])) {
        $errors['visit-date'] = "Visit date is required.";
    }

    $values['total-traveler'] = $_POST['total-traveler'] ?? '0';
    if (!is_numeric($values['total-traveler']) || intval($values['total-traveler']) < 1) {
        $errors['total-traveler'] = "You must have at least 1 traveler.";
    } else {
        $values['total-traveler'] = intval($values['total-traveler']);
    }

    $values['interests'] = $_POST['interests'] ?? [];
    if (empty($values['interests'])) {
        $errors['interests'] = "Please select at least one tour/event.";
    }

    $values['contact-method'] = $_POST['contact-method'] ?? [];
    if (empty($values['contact-method'])) {
        $errors['contact-method'] = "Please select at least one contact method.";
    }

    $values['callback-time'] = $_POST['callback-time'] ?? '';
    if (empty($values['callback-time'])) {
        $errors['callback-time'] = "Please select the best time for a call-back.";
    }

    $values['extra-info'] = clean_input($_POST['extra-info'] ?? '');
    $values['referral'] = clean_input($_POST['referral'] ?? '');

    if (isset($_FILES['nid']) && $_FILES['nid']['error'] != UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['nid'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['nid'] = "Error uploading file.";
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowedTypes)) {
                $errors['nid'] = "Only JPG and PNG images are allowed.";
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                $errors['nid'] = "File size must be less than 2MB.";
            }
        }
    } else {
        $errors['nid'] = "Please upload your NID photo.";
    }

    if (empty($errors)) {
        $priceMap = [
            'Revolution' => 50,
            'Transcendentalism' => 40,
            'Custom' => 100,
            'Ghosts' => 60,
            'Graveyards' => 30,
            'Tavern' => 20
        ];

        foreach ($values['interests'] as $tour) {
            if (isset($priceMap[$tour])) {
                $totalPrice += $priceMap[$tour];
            }
        }
        $totalPrice *= $values['total-traveler'];

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $targetFile = $uploadDir . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $targetFile);

        $successMsg = "Form submitted successfully! Total Price for {$values['total-traveler']} traveler(s): $" . number_format($totalPrice, 2);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tour Reservation Form</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        h1 {
            font-size: 85px;
            text-align: left;
            font-weight: 700;
            font-family: sans-serif;
            opacity: 0.8;
            margin-bottom: 0px;
        }

        #statement {
            font-family: sans-serif;
            font-size: 45px;
            font-weight: 100;
            color: black;
            opacity: 0.4;
            margin-top: 0px;
        }

        .block {
            margin: 100px;
        }

        hr {
            width: 100vw;
            margin: 100px 0 80px 0;
            background-color: grey;
            height: 0.6px;
            opacity: 0.4;
        }


        label {
            font-family: sans-serif;
            font-size: 45px;
            opacity: 0.8;
        }

        input {
            margin-top: 30px;
            width: 850px;
            height: 70px;
            border-radius: 10px;
            opacity: 0.4;
            font-size: 45px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            margin-bottom: 150px;
            align-items: flex-start;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .inputs-row {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .inputs-row input {
            flex: 1;
            height: 70px;
            border-radius: 10px;
            opacity: 0.4;
        }

        .checkbox-row {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 20px;
        }

        .checkbox-group {
            display: flex;
            gap: 6px;
            align-items: center;
            opacity: 0.8;
            margin-left: -400px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.8;
            margin: 4px 0;
            margin-left: -400px;
        }

        .checkbox-group input,
        .radio-option input {
            margin-right: -370px;
            vertical-align: middle;
        }

        textarea {
            height: 400px;
            width: 1780px;
            margin-top: 30px;
            border-radius: 10px;
            opacity: 0.4;
            font-size: 45px;
            resize: none;
        }

        #submit {
            font-size: 45px;
            font-weight: 500;
            color: white;
            background-color: #18bd5b;
            border: none;
            border-radius: 8px;
            padding: 30px 100px;
            cursor: pointer;
            margin-top: 80px;
            margin-left: 800px;
            margin-bottom: 80px;
        }

        .change {
            font-size: 45px;
            font-weight: 500;
            color: white;
            background-color: #18bd5b;
            border: none;
            border-radius: 8px;
            padding: 30px 100px;
            cursor: pointer;
        }

        button:hover {
            background-color: #04AA6D;
            color: white;
        }

        .options {
            font-size: 40px;
        }

        .error {
            color: red;
            font-size: 30px;
            margin-top: 5px;
        }

        .success {
            font-size: 35px;
            color: green;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="block">
        <h1>Tour Reservation Form</h1>
        <p id="statement">Lets know what you are interested to see!</p>
    </div>
    <hr>
    <div class="block">
        <?php if ($successMsg): ?>
            <div class="success"><?= $successMsg ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="first-name">Full Name</label>
                    <div class="inputs-row">
                        <input type="text" id="first-name" name="first-name" style="margin-right: 55px;" value="<?= htmlspecialchars($values['first-name']) ?>">
                        <input type="text" id="last-name" name="last-name" value="<?= htmlspecialchars($values['last-name']) ?>">
                    </div>
                    <?php if(isset($errors['first-name'])): ?><div class="error"><?= $errors['first-name'] ?></div><?php endif; ?>
                    <?php if(isset($errors['last-name'])): ?><div class="error"><?= $errors['last-name'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder=" ex: myname@gmail.com" style="margin-right: 55px;" value="<?= htmlspecialchars($values['email']) ?>">
                    <?php if(isset($errors['email'])): ?><div class="error"><?= $errors['email'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="phone-number">Phone Number</label>
                    <input type="tel" id="phone-number" name="phone-number" placeholder=" (000)000-0000" value="<?= htmlspecialchars($values['phone-number']) ?>">
                    <?php if(isset($errors['phone-number'])): ?><div class="error"><?= $errors['phone-number'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date">When are you planning to visit?</label>
                    <input type="date" id="date" name="visit-date" style="margin-right: 55px; padding-left: 15px;" value="<?= htmlspecialchars($values['visit-date']) ?>">
                    <?php if(isset($errors['visit-date'])): ?><div class="error"><?= $errors['visit-date'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="total-traveler">How many people are in your group?</label>
                    <input type="number" id="total-traveler" name="total-traveler" style="margin-right: 55px;" value="<?= htmlspecialchars($values['total-traveler']) ?>" min="0">
                    <?php if(isset($errors['total-traveler'])): ?><div class="error"><?= $errors['total-traveler'] ?></div><?php endif; ?>
                </div>
            </div>

            <div>
                <button type="button" class="change" onclick="increaseVisitor()">+</button>
                <button type="button" class="change" onclick="decreaseVisitor()">-</button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nid">Upload NID Photo</label>
                    <input type="file" id="nid" name="nid" accept="image/png, image/jpeg">
                    <?php if(isset($errors['nid'])): ?><div class="error"><?= $errors['nid'] ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Which tours or events are you most interested in?</label>
                    <?php
                    $tours = [
                        'Revolution' => 'Revolution was just the beginning ($50)',
                        'Transcendentalism' => 'Transcendentalism ($40)',
                        'Custom' => 'Custom ($100)',
                        'Ghosts' => 'Ghosts in the Gloaming ($60)',
                        'Graveyards' => 'Gateposts, Grapes & Graveyards ($30)',
                        'Tavern' => 'Tavern Life ($20)'
                    ];
                    foreach ($tours as $key => $label) {
                        $checked = in_array($key, $values['interests']) ? 'checked' : '';
                        $price = match ($key) {
                            'Revolution' => 50,
                            'Transcendentalism' => 40,
                            'Custom' => 100,
                            'Ghosts' => 60,
                            'Graveyards' => 30,
                            'Tavern' => 20,
                            default => 0,
                        };
                        echo '<div class="checkbox-group">';
                        echo '<input type="checkbox" id="tour-'.strtolower($key).'" name="interests[]" value="'.htmlspecialchars($key).'" data-price="'.$price.'" '.$checked.'>';
                        echo '<label for="tour-'.strtolower($key).'" class="options">'.htmlspecialchars($label).'</label>';
                        echo '</div>';
                    }
                    if (isset($errors['interests'])) {
                        echo '<div class="error">'.$errors['interests'].'</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>What is the best way to contact you?</label>
                    <?php
                    $contacts = ['Phone', 'Email', 'Either'];
                    foreach ($contacts as $contact) {
                        $checked = in_array($contact, $values['contact-method']) ? 'checked' : '';
                        echo '<div class="checkbox-group">';
                        echo '<input type="checkbox" id="contact-'.strtolower($contact).'" name="contact-method[]" value="'.$contact.'" '.$checked.'>';
                        echo '<label for="contact-'.strtolower($contact).'" class="options">'.$contact.'</label>';
                        echo '</div>';
                    }
                    if (isset($errors['contact-method'])) {
                        echo '<div class="error">'.$errors['contact-method'].'</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>If phone, when is the best time of day for a call-back?</label>
                    <?php
                    $times = ['8–10 a.m.', '10 a.m.–12 p.m.', '12–2 p.m.', '2–4 p.m.', '4–6 p.m.'];
                    foreach ($times as $time) {
                        $checked = ($values['callback-time'] === $time) ? 'checked' : '';
                        echo '<div class="radio-option">';
                        echo '<input type="radio" id="time-'.md5($time).'" name="callback-time" value="'.$time.'" '.$checked.'>';
                        echo '<label for="time-'.md5($time).'" class="options">'.$time.'</label>';
                        echo '</div>';
                    }
                    if (isset($errors['callback-time'])) {
                        echo '<div class="error">'.$errors['callback-time'].'</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Anything else you want us to know?</label>
                    <textarea name="extra-info" placeholder="Type here..."><?= htmlspecialchars($values['extra-info']) ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>How did you hear about us?</label>
                    <input type="text" name="referral" value="<?= htmlspecialchars($values['referral']) ?>" placeholder="Type here...">
                </div>
            </div>

            <button type="submit" id="submit">Submit</button>
        </form>
    </div>

    <script>
        const totalTravelerInput = document.getElementById('total-traveler');
        function increaseVisitor() {
            totalTravelerInput.value = parseInt(totalTravelerInput.value || 0) + 1;
        }
        function decreaseVisitor() {
            let current = parseInt(totalTravelerInput.value || 0);
            if (current > 0) {
                totalTravelerInput.value = current - 1;
            }
        }
    </script>

</body>

</html>
