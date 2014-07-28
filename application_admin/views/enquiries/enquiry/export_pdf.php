<?php
$this->pdf->setFont('tahoma', '', 7);
$this->pdf->setX(140);
$thwidth = 240;
$tdwidth = 390;
$output = '
<table cellpadding="8" border="1" style="font-size: 7pt;">
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Date Created</strong></th>
        <td width="'.$tdwidth.'">'.$enquiry_data["enquiry_creation_date"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Date Due</strong></th>
        <td width="'.$tdwidth.'">'.$enquiry_data["enquiry_due_date"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Enquiry Ref</strong></th>
        <td width="'.$tdwidth.'">'.$enquiry_data["enquiry_id"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Status</strong></th>
        <td width="'.$tdwidth.'">'. $enquiry_data["enquiry_status"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Priority</strong></th>
        <td width="'.$tdwidth.'">'.$enquiry_data["enquiry_priority"].'</td>
    </tr>
</table>';
$this->pdf->writeHTML($output);

$this->pdf->setFont('tahoma', '', 11);

$thwidth = 550;
$tdwidth = 1500;

$this->pdf->call_method('moveY', array(-15));

if (has_capability('enquiries:editenquiries')) : ?>
    <?=$this->pdf->call_method('moveY', array(-5)); ?>
    <h1>Company Details</h1>
    <?=$this->pdf->call_method('moveY', array(-2)); ?>
    <table cellpadding="8" border="1">
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Enquiring Company</strong></th>
            <td width="<?=$tdwidth?>"><?=$enquiry_data['company_details']?></td>
        </tr>
    </table>

    <?=$this->pdf->call_method('moveY', array(-5)); ?>
    <h1>Contact Details</h1>
    <?=$this->pdf->call_method('moveY', array(-2)); ?>
    <table cellpadding="8" border="1">
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Contact Name</strong></th>
            <td width="<?=$tdwidth?>"><?php echo "{$enquiry_data['user_salutation']} {$enquiry_data['user_first_name']} {$enquiry_data['user_surname']}";?></td>
        </tr>
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Phone</strong></th>
            <td width="<?=$tdwidth?>"><?=$enquiry_data['user_phone']?></td>
        </tr>
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Email</strong></th>
            <td width="<?=$tdwidth?>"><?=$enquiry_data['user_email']?></td>
        </tr>
        <?php if (!empty($enquiry_data['user_fax'])) : ?>
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Fax</strong></th>
            <td width="<?=$tdwidth?>"><?=$enquiry_data['user_fax']?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($enquiry_data['user_mobile'])) : ?>
        <tr>
            <th width="<?=$thwidth?>" style="text-align: right"><strong>Mobile</strong></th>
            <td width="<?=$tdwidth?>"><?=$enquiry_data['user_mobile']?></td>
        </tr>
        <?php endif; ?>
    </table>
<?php endif; ?>

<?=$this->pdf->call_method('moveY', array(-6)); ?>
<h1>Product Details</h1>
<?=$this->pdf->call_method('moveY', array(-3)); ?>
<?=$this->pdf->horizontal_table($product_data, $thwidth, $tdwidth); ?>

<?=$this->pdf->call_method('moveY', array(2)); ?>
<h1>Trading Details</h1>
<?=$this->pdf->call_method('moveY', array(-3)); ?>
<?=$this->pdf->horizontal_table($trading_data, $thwidth, $tdwidth); ?>
