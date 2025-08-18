<!DOCTYPE html>
<html>
<body>

<?php
$sum = 0;
$numbers = array(2, 3, 5, 10, 9, 7, 4);
for($i = 0; $i < 7; $i++) 
{
    $sum = $sum + $numbers[$i];
}

echo nl2br("The sum of numbers are $sum \n");


$biggest = $numbers[0]; 

for($i = 0; $i < 6; $i++) 
{
    for($j = $i+1; $j < 7; $j++)
    {
        if($numbers[$i] < $numbers[$j])
        {
            $biggest = $numbers[$i];
            $numbers[$i] = $numbers[$j];
            $numbers[$j] = $biggest;
        }
    }
    //echo nl2br("The number of the array in order is $numbers[$i] \n");
}

echo nl2br("The Second highest number in the array is $numbers[1] \n");


for($i = 0; $i < 10; $i++)
{
    for($j = 0; $j < $i + 1; $j++)
    {
        echo nl2br("*");
    }
    echo nl2br("\n");
}



$sentence = "This is a string";
$revSentence = strrev($sentence);

echo nl2br("$revSentence \n");


$word = "American";
$vowel = array("a", "e", "i", "o", "u");
$vowelList = array();
$consonentList = array();

for ($i = 0; $i < strlen($word); $i++) {
    if (in_array(strtolower($word[$i]), $vowel)) {
        $vowelList[] = $word[$i];
    } else {
        $consonentList[] = $word[$i];
    }
}

echo nl2br("Vowels: ");
foreach ($vowelList as $v) {
    echo nl2br("$v");
}

echo nl2br("\n");

echo nl2br("Consonants: ");
foreach ($consonentList as $c) {
    echo nl2br("$c");
}


?> 

</body>
</html>
