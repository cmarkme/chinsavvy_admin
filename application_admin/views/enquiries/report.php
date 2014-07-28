<style type="text/css">
th {
    font-weight: bold;
    border-bottom: 2px solid #000000;
    background-color: #bbb;
    text-align: left;
}

.country { width: 280px; }
.state { width: 220px; }
.enquiry_id { width: 180px; }
.creation_date { width: 210px; }
.enquirer { width: 280px; }
.first_name { width: 220px; }
.surname { width: 220px; }
.product_title { width: 320px; }
.email { width: 510px; }
.phone { width: 220px; }
.mobile { width: 220px; }
.odd { background-color: #eee; }
.even { background-color: #fff; }

</style>
<table cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr>
            <th class="country">Country</th>
            <th class="state">State</th>
            <th class="enquiry_id">Enquiry #</th>
            <th class="creation_date">Enquiry Date</th>
            <th class="enquirer">Enquirer</th>
            <th class="first_name">First Name</th>
            <th class="surname">Last Name</th>
            <th class="product_title">Product Title</th>
            <th class="email">Email Address</th>
            <th class="phone">Phone</th>
            <th class="mobile">Mobile</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($enquiries AS $key => $enquiry) : ?>
        <tr class="country <?php echo ($key % 2) ? 'odd' : 'even'?>">
            <td class="country"><?=$enquiry->country?></td>
            <td class="state"><?=$enquiry->state?></td>
            <td class="enquiry_id"><?=$enquiry->enquiry_id?></td>
            <td class="creation_date"><?=$enquiry->enquiry_creation_date?></td>
            <td class="enquirer"><?=$enquiry->enquirer?></td>
            <td class="first_name"><?=$enquiry->first_name?></td>
            <td class="surname"><?=$enquiry->surname?></td>
            <td class="product_title"><?=$enquiry->product_title?></td>
            <td class="email"><?=$enquiry->email?></td>
            <td class="phone"><?=$enquiry->phone?></td>
            <td class="mobile"><?=$enquiry->mobile?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
