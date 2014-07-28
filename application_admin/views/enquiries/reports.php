<?=$section_title?>
<div id="reports">
<form action="/enquiries/report/generate_pdf" method="post">
<table class="tbl">
    <thead>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Sorting field</th>
        <th>Sorting direction</th>
        <th>Generate PDF</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Overdue quotations</td>
        <td>Shows all the enquiries with status PENDING or SOURCING STARTED that have not been quoted at least 5 days since enquiry date</td>
        <td>
            <select name="overdue_sort" id="overdue_sort">
                <option value="country">Country of enquirer</option>
                <option value="state">State/Province of enquirer</option>
                <option value="enquiries.creation_date">Date of enquiry</option>
            </select>
        </td>
        <td>
            <select name="overdue_direction" id="overdue_direction">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </td>
        <td><input type="submit" name="submit_overdue" value="Generate PDF report" /></td>
    </tr>
<?php $report_code = 'pending'.ENQUIRIES_REPORT_PENDING_30; ?>
    <tr>
        <td>Pending quotations (first 30 days)</td>
        <td>Shows all the enquiries with status CUSTOMER QUOTED that were quoted in the last 30 days</td>
        <td>
            <select name="<?=$report_code?>_sort" id="<?=$report_code?>_sort">
                <option value="country">Country of enquirer</option>
                <option value="state">State/Province of enquirer</option>
                <option value="enquiries.creation_date">Date of enquiry</option>
            </select>
        </td>
        <td>
            <select name="<?=$report_code?>_direction" id="<?=$report_code?>_direction">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </td>
        <td><input type="submit" name="submit_<?=$report_code?>" value="Generate PDF report" /></td>
    </tr>
<?php $report_code = 'pending'.ENQUIRIES_REPORT_PENDING_90; ?>
    <tr>
        <td>Pending quotations (31-90 days)</td>
        <td>Shows all the enquiries with status CUSTOMER QUOTED that were quoted between 31 and 90 days from today</td>
        <td>
            <select name="<?=$report_code?>_sort" id="<?=$report_code?>_sort">
                <option value="country">Country of enquirer</option>
                <option value="state">State/Province of enquirer</option>
                <option value="enquiries.creation_date">Date of enquiry</option>
            </select>
        </td>
        <td>
            <select name="<?=$report_code?>_direction" id="<?=$report_code?>_direction">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </td>
        <td><input type="submit" name="submit_<?=$report_code?>" value="Generate PDF report" /></td>
    </tr>
<?php $report_code = 'pending'.ENQUIRIES_REPORT_PENDING_180; ?>
    <tr>
        <td>Pending quotations (91-180 days)</td>
        <td>Shows all the enquiries with status CUSTOMER QUOTED that were quoted between 91 and 180 days from today</td>
        <td>
            <select name="<?=$report_code?>_sort" id="<?=$report_code?>_sort">
                <option value="country">Country of enquirer</option>
                <option value="state">State/Province of enquirer</option>
                <option value="enquiries.creation_date">Date of enquiry</option>
            </select>
        </td>
        <td>
            <select name="<?=$report_code?>_direction" id="<?=$report_code?>_direction">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </td>
        <td><input type="submit" name="submit_<?=$report_code?>" value="Generate PDF report" /></td>
    </tr>
    </tbody>
</table>
</form>
</div>
