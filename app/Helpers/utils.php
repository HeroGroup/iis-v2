<?php

function customDecode($input)
{
    $outputArray = [];
    $inputLength = strlen($input);

    for($i=0;$i<$inputLength-1;$i+=2) {
        $outputArray[$i] = $input[$i + 1];
        $outputArray[$i+1] = $input[$i];
    }

    $outputArray = array_reverse($outputArray);

    $outputLength = count($outputArray);
    $output = "";
    for($j=0;$j<$outputLength;$j++)
        $output .= $outputArray[$j];

    return $output;
}
