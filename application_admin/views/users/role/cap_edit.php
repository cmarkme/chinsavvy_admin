<?php echo $add_title ?>
<div id="add">
    <ul id="assignable">
    <?php print_recursive_list($assignable_caps, 'label', 'children', null, "users/role/add_cap_to_role/$role_id/", 'id'); ?>
    </ul>
</div>
<?php echo $list_title ?>
<div id="page">
    <div id="userroletable">
        <table id="ajaxtable" class="tbl">
            <thead>
                <tr>
                    <th>Cap ID</th>
                    <th>Name</th>
                    <th>Included capabilities</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($capabilities as $cap) : ?>
                <tr>
                    <td valign="top"><?=$cap->id?></td>
                    <td valign="top"><?=$cap->label?></td>
                    <td><ul>
                    <?php foreach ($dependencies[$cap->id] as $dependent_cap) : ?>
                        <li><?=$dependent_cap->label?></li>
                    <?php endforeach; ?>
                    </ul></td>
                    <td valign="top">
                        <a href="/users/role/delete_role_cap/<?=$role_id?>/<?=$cap->id?>">
                            <img class="icon" src="images/admin/icons/delete_16.gif" title="Remove this capability from this role" onclick="return deletethis();"/>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="add_div"></div>
</div>
