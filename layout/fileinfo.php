<? if($private) var_dump($info); ?>
<table id="fileinfo">
  <tr>
    <td>Filename: </td>
    <td><?= string_to_html($info['Filename']) ?><td>
  </tr>
  <tr>
    <td>Size: </td>
    <td><?= string_bytes_to_filesize($info['Size']) ?><td>
  </tr>
</table>
