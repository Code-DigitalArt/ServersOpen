<?php

require_once '../app/Mage.php';
Mage::app();

// $importGiftFile = 'giftcardaccounts-boulder-active-only.csv';
$importGiftFile = 'giftcardaccounts.csv';
$importGiftCsv = new Varien_File_Csv();
$giftCardsData = $importGiftCsv->getData($importGiftFile);

echo "<pre>";
$header = $giftCardsData[0];
unset($giftCardsData[0]);

$giftCards = array();

foreach ($giftCardsData as $giftCard) {
    $temp = array();
    $temp[$header[0]] = $giftCard[0]; // Id
    $temp[$header[1]] = $giftCard[1]; // Code
    $temp[$header[2]] = $giftCard[2]; // Store
    $temp[$header[3]] = $giftCard[3]; // Date
    $temp[$header[4]] = $giftCard[4]; // Exp
    $temp[$header[5]] = $giftCard[5]; // Active
    $temp[$header[6]] = $giftCard[6]; // Status
    $temp[$header[7]] = $giftCard[7]; // Balance
    $giftCards[] = $temp;
}

$GiftCard = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
function getState($status) {
    global $GiftCard;
    switch ($state) {
        case 'Available';
            return $GiftCard::STATE_AVAILABLE;
            break;
        case 'Used';
            return $GiftCard::STATE_USED;
            break;
        case 'Redeemed';
            return $GiftCard::STATE_REDEEMED;
            break;
        case 'Expired';
            return $GiftCard::STATE_EXPIRED;
            break;
    }
}
function getActive($active) {
    global $GiftCard;
    switch ($active) {
        case 'Yes';
            return $GiftCard::STATUS_ENABLED;
            break;
        case 'No';
            return $GiftCard::STATUS_DISABLED;
            break;
    }
}

foreach ($giftCards as $_giftCard) {
    $gift_card = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');

    $expired = false;
    if ($_giftCard['Expiration Date'] !== '--') {
        if (strtotime($_giftCard['Expiration Date']) < time()) {
            $expired     = true;
            $dateExpires = '';
        } else {
            $dateExpires = $_giftCard['Expiration Date'];
        }
    } else {
        $dateExpires = '';
    }

        
    $status      = $expired ? $GiftCard::STATUS_DISABLED : getActive($_giftCard['Active']);

    $gift_card
            ->setStatus($status)
            ->setActive($status)
            ->setState(getState($_giftCard['Status']))

            ->setCode($_giftCard['Code'])
            ->setBalance(str_replace("$", "", $_giftCard['Balance']))

            ->setWebsiteId(1)            
            ->setDateExpires($dateExpires)
            ->setIsRedeemable(getActive($_giftCard['Active']));
    try {
        $gift_card->save();
        echo "Gift Card with code '" . $_giftCard['Code'] . "' successfully Saved <br>";
    } catch (Exception $ex) {
        echo "Error While saving : " . $ex->getMessage() . "<br>";
    }
}