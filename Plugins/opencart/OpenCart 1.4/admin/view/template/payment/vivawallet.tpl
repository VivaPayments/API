<?php echo $header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
  <div class="left"></div>
  <div class="right"></div>
  <div class="heading">
    <h1 style="background-image: url('view/image/payment.png');"><?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
  <div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
			<table class="form">
				<tr>
					<td><span class="required">*</span> <?php echo $entry_merchantid; ?></td>
					<td>
						<input type="text" name="vivawallet_merchantid" value="<?php echo $vivawallet_merchantid; ?>" />
						<?php if ($error_merchantid) { ?>
						<span class="error"><?php echo $error_merchantid; ?></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_merchantpass; ?></td>
					<td>
						<input type="text" name="vivawallet_merchantpass" value="<?php echo $vivawallet_merchantpass; ?>" />
						<?php if ($error_merchantpass) { ?>
						<span class="error"><?php echo $error_merchantpass; ?></span>
						<?php } ?>
					</td>
				</tr>
                <tr>
					<td><?php echo $entry_source; ?></td>
					<td>
						<input type="text" name="vivawallet_source" value="<?php echo $vivawallet_source; ?>" />
					</td>
				</tr>
                <tr>
					<td><?php echo $entry_maxinstal; ?></td>
					<td>
						<input type="text" name="vivawallet_maxinstal" value="<?php echo $vivawallet_maxinstal; ?>" />
                        <?php echo $text_instalments; ?>
					</td>
				</tr>
		<tr>
          <td><?php echo $entry_orderurl; ?></td>
          <td><input type="text" name="vivawallet_orderurl" size="50" value="<?php echo $vivawallet_orderurl; ?>" />
          </td>
        </tr> 
        
		<tr>
          <td><?php echo $entry_url; ?></td>
          <td><input type="text" name="vivawallet_url" size="50" value="<?php echo $vivawallet_url; ?>" />
          </td>
        </tr> 

		<!-- New statuses... -->
		<tr>
          <td><?php echo $entry_processed_status; ?></td>
          <td><select name="vivawallet_processed_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $vivawallet_processed_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>

        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="vivawallet_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $vivawallet_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="vivawallet_status">
              <?php if ($vivawallet_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="vivawallet_sort_order" value="<?php echo $vivawallet_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>