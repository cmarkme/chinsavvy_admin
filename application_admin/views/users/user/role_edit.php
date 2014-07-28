<?php echo $add_title ?>
<div id="add">
    <?=print_dropdown_element('new_role', 'New role', $available_roles, false, 'onchange="window.location=\'/users/user/add_role/'.$user_id.'/\'+this.value;"')?>
</div>
<?php echo $roles_title ?>
<div id="roles">
    <div id="userroletable">
        <table id="ajaxtable" class="tbl">
            <thead>
                <tr>
                    <th>Cap ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($user_roles as $role) : ?>
                <tr>
                    <td valign="top"><?=$role->id?></td>
                    <td valign="top"><?=$role->name?></td>
                    <td valign="top"><?=$role->description?></td>
                    <td valign="top">
                        <a href="/users/user/delete_user_role/<?=$user_id?>/<?=$role->id?>">
                            <img class="icon" src="images/admin/icons/delete_16.gif" title="Remove this role from this user" onclick="return deletethis();"/>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $capabilities_title ?>
<div id="capabilities">
    <table id="usercaplist" class="tbl">
        <thead>
            <tr>
                <th>Cap ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Enabled</th>
                <th>Roles giving this capability (click to add)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($all_caps as $cap) : ?>
            <tr class="<?=(in_array($cap->name, $user_capabilities)) ? 'enabled' : 'disabled'?>">
                <td><?=$cap->id?></td>
                <td><?=$cap->name?></td>
                <td><?=$cap->label?></td>
                <td><?=(in_array($cap->name, $user_capabilities)) ? 'Yes' : 'No'?></td>
                <td>
                    <?php if (!in_array($cap->name, $user_capabilities)) : ?>
                    <ul>
                        <?php foreach ($cap_roles[$cap->id] as $role_id => $role) : ?>
                            <?php if (array_key_exists($role_id, $available_roles)) : ?>
                            <li><a href="/users/user/add_role/<?=$user_id?>/<?=$role_id?>" title="Click to add this role to this user"><?=$role->name?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </td>
        <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>
