<?php echo $add_title ?>
<div id="add_div"></div>

<?php echo $list_title ?>
<div id="page">
    <div id="userroletable">
        <table id="ajaxtable" class="tbl" style="float: left;">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First name</th>
                    <th>Last name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?=$user->id?></td>
                    <td><?=$user->first_name?></td>
                    <td><?=$user->surname?></td>
                    <td>
                        <a href="/users/role/delete_role_user/<?=$role_id?>/<?=$user->id?>">
                            <img class="icon" src="images/admin/icons/delete_16.gif" title="Remove this user from this role" onclick="return deletethis();"/>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
