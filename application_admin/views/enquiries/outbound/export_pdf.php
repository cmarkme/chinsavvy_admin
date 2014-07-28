<?php
$this->pdf->setFont('tahoma', '', 10);
$thwidth = 240;
$tdwidth = 180;
$this->pdf->setX(159);
$output = '
<table cellpadding="8" style="background-color: #DDDDDD;"> <tr> <td width="480px">
<p style="font-size: 11pt; background-color: #DDDDDD;">Chinasavvy HK Ltd</p>
<p style="font-size: 8pt;">400 Wuzi Building<br />
Beijiaochang Henglu 12<br />
Guangzhou 510050<br />
P.R. China<br />
<br />
Tel: +86 20 8388 7080<br />
Fax: +86 20 8388 7425<br />
Email: info@chinasavvy.com<br />
Website: www.chinasavvy.com</p>
</td></tr></table>
';
$this->pdf->writeHTML($output); 
$this->pdf->setY(28);
$output = '
<table cellpadding="8" border="0" style="font-size: 7pt; background-color: #DDDDDD;">
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Date</strong></th>
        <td width="'.$tdwidth.'">'.$quotation_data["quotation_creation_date"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Quotation No</strong></th>
        <td width="'.$tdwidth.'">'.$quotation_data["quotation_id"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Enquiry Ref No</strong></th>
        <td width="'.$tdwidth.'">'.$quotation_data["quotation_enquiry_id"].'</td>
    </tr>
</table>';
$this->pdf->writeHTML($output);
$this->pdf->setFont('tahoma', '', 10);
$this->pdf->setY(28);
$thwidth = 550;
$tdwidth = 1500; 

echo $this->pdf->horizontal_table(
    array('To:' => $company_info .'<br />',
          'Attention:' => "{$quotation_data['customer_salutation']} {$quotation_data['customer_first_name']} {$quotation_data['customer_surname']}<br /><br />We have pleasure in quoting you as follows:<br />"),
          $thwidth, 1000, 6, 0, 100); 
echo $this->pdf->horizontal_table($quotation_info, $thwidth, $tdwidth, 8, 0);
?>

