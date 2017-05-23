<html>
<?php
session_start();
$homeValue = fopen("propertyValues.csv","r"); // Open the list of property values, separated by commas in read mode. Sorted from greatest to least. 
$numberOfHomes = sizeof($homeValue);
    if (isnull($_SESSION['totalTax'])) {
        
        // If a variable is undefined, we are starting a new session
        // so we want to work with the 2016 values
        //
        
        $totalTax = 1583684; // Total Revenue from Property Tax
        $taxRate = .67258; // Current Tax rate in Richwood in %
        $exemption = 25000; // Current property tax exemption, subtracted before tax is calculated
        $toCalculate = "total";
        
    }       elseif ($_SESSION['toCalculate'] = "total") { // If it is not a new session, we must be calculating one of the three values. 

            $taxRate = $_SESSION['taxRate']; //Two of the values were set by the form and are already saved in the session. 
            $exemption = $_SESSION['exemption'];
            
            $totalTax = 0; // We will calculate it as we go
           for ($i=0; $i<$numberOfHomes;$i++) {
               $taxedValue = $homeValue[$i]-$exemption;  // By doing it here, I only have to calculate it once.  
                 if ($taxedValue > 0) {
                 $totalTax = $totalTax + $taxRate * ($taxedValue);
                 } else {
                   $i=$numberOfHomes; // There is no point continuing to calculate home that are cheaper than the exemption. 
               }
                }
    }        elseif ($_SESSION['toCalculate'] = "taxRate") { //This one is the simplest. Total Tax / Taxable Property = Tax Rate
        
        $taxableProperty = 0; 
         for ($i=0; $i<$numberOfHomes;$i++) {
             $taxedValue = $homeValue[$i] - $exemption;
               if ($taxedValue>0) {
                 $taxableProperty = $taxableProperty + ($homeValue[$i]-$exemption);
               } else {
                   $i=$numberOfHomes; 
               }
                }
        $taxRate = $totalTax/$taxableProperty;
        
            }   elseif ($_SESSION['toCalculate'] = "exemption") { // This can't be solved analytically, so I use the bisection method to estimate it to arbitrary precision. 
        
        $epsilon = 100; // This will find the exemption that puts the overall budget within epsilon dollars of the target. 
                       // More precision takes much more computation time, so a large-ish number for playing around makes sense.
        
        
        $maxGuess = $homeValue[0]; // Since the values are sorted, this sets the highest possible exemption. Anything higher obviously has no tax base. 
        $minGuess = 0; // The lowest possible exemption is obviously 0
        $exemptionGuess = 50000; //Naively, we would set this as the average of the max and min. But we may gain a little ground by guessing more realistically. 
        $raisedRevenue = 0;
        while (abs(@raisedRevenue-$totalTax)<$epsilon) {
            for ($i=o;$i<$numberOfHomes;$i++) {
                $taxedValue = $homeValue[$i] - $exemptionGuess;
                if ($taxedValue>0) {
                    $raisedRevenue = ($taxedValue)*$taxRate + $raisedRevenue;
                } else {
                    $i=$numberOfHomes;
                }
            }
        $shortfall = $totalTax - $raisedRevenue;
        if ($shortfall>$epsilon) {
            $minGuess = $exemptionGuess;
            $exemptionGuess = .5*($minGuess+$maxGuess);
        } elseif($shortfall<0-$epsilon){
            $maxGuess=$exemptionGuess;
            $exemptionGuess = .5*($minGuess + $maxGuess);
            }
        else {
            $exemption = $exemptionGuess;
        }}
        
    }
    ?>
    <head>
    <title>Tax Rate</title></head>
<body>
    <form action="TaxCalculator.php" method="post">
        <table><tr>
            <td><b>Total Tax Revenue</b></td><td><input type="text" name="$totalTax" value="<?php echo $totalTax ?>"></td><td><input type="radio" name="toCalculate" value="total"></td> </tr>
            <tr><td><b>Tax Rate</b></td><td><input type="text" name="$taxRate" value="<?php echo $taxRate ?>"></td><td><input type="radio" name="toCalculate" value="taxRate"></td></tr>
             <tr><td><b>Exemption</b></td><td><input type="text" name="$exemption" value="<?php echo $exemption ?>"></td><td><input type="radio" name="toCalculate" value="exemption"></td></tr>
            
        </table>
    </form> 
</body>
</html>