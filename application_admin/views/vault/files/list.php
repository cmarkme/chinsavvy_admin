<div>
    <table id="ajaxtable" class="tbl">
    <thead>
        <tr>
            <th class="file_name">File name</th>
            <th class="file_size">File size</th>
            <th class="file_size_human">File size</th>
            <th class="version">Version</th>
            <th class="customer_name">Customer</th><!--TODO: only display customer if proper capability is present -->
            <th class="enquiry_id">Enquiry ID</th>
            <th class="revision_date">Last modified timestamp</th>
            <th class="revision_date_human">Last Modified</th>
            <th class="staff">Modified by</th>
            <th class="identity">Identity</th>
            <th class="type">Type</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($files as $file) : ?>
            <?php foreach ($file['versions'] as $key => $version) : ?>
            <tr style="<?=($key > 0) ? 'display: none;' : ''?>" file_id="<?=$file['file_id']?>" file_version_id="<?=$version['file_version_id']?>">
                <td><?=$version['new_name']?></td>
                <td><?=$version['file_size']?></td>
                <td><?=$version['file_size_human']?></td>
                <td>
                    <select class="file_version_select">
                    <?php foreach ($file['versions'] as $select_version) : ?>
                        <option <?=($select_version['file_version_id'] == $version['file_version_id']) ? 'selected="selected"' : ''?>
                            value="<?=$select_version['file_version_id']?>"><?=$select_version['version']?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                <td><?=$file['customer_first_name'] . ' ' . $file['customer_surname']?></td>
                <td><?=$file['enquiry_id']?></td>
                <td><?=$version['revision_date']?></td>
                <td><?=$version['revision_date_human']?></td>
                <td><?=$version['staff_first_name'] . ' ' . $version['staff_surname']?></td>
                <td><?=$file['identity_human']?></td>
                <td><?=$file['type_human']?></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
    </table>
</div>
