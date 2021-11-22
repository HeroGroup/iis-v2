function customDecode(input) {
    let outputArray = [];
    let inputLength = input.length;

    for(let i=0;i<inputLength-1;i+=2) {
        outputArray[i] = input[i + 1];
        outputArray[i+1] = input[i];
    }

    outputArray.reverse();

    let output = "";
    for(let j=0;j<inputLength;j++)
        output += outputArray[j];

    return output;
}
