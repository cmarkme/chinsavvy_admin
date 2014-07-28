<?php $small_width = 180; ?>
<tr>
    <td width="160"><?=$process_number . '.' . $sub_number.$spec_number?></td>
    <td colSpan="3" width="1050"><?=stripslashes($spec['data'])?></td>
    <td width="<?=$small_width?>"><?=$importance?></td>
    <td width="<?=$small_width?>"><?=$checked?></td>
    <td width="<?=$small_width?>"><?=$critical?></td>
    <td width="<?=$small_width?>"><?=$major?></td>
    <td width="<?=$small_width?>"><?=$minor?></td>
</tr>
