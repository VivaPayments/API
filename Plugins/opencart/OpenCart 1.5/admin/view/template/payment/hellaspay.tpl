<?php echo $header; ?>
<div id="content">

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<?php if ($error_warning) { ?>
	<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>

	<div class="box">
		<div class="heading">
			<h1><img src="view/image/payment.png" alt="Viva Payments" /><?php echo $heading_title; ?></h1>
			<div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
		</div>
	
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
			<table class="form">
				<tr>
					<td><span class="required">*</span> <?php echo $entry_merchantid; ?></td>
					<td>
						<input type="text" name="hellaspay_merchantid" value="<?php echo $hellaspay_merchantid; ?>" />
						<?php if ($error_merchantid) { ?>
						<span class="error"><?php echo $error_merchantid; ?></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_merchantpass; ?></td>
					<td>
						<input type="text" name="hellaspay_merchantpass" value="<?php echo $hellaspay_merchantpass; ?>" />
						<?php if ($error_merchantpass) { ?>
						<span class="error"><?php echo $error_merchantpass; ?></span>
						<?php } ?>
					</td>
				</tr>
                <tr>
					<td><?php echo $entry_source; ?></td>
					<td>
						<input type="text" name="hellaspay_source" value="<?php echo $hellaspay_source; ?>" />
					</td>
				</tr>
                <tr>
					<td><?php echo $entry_maxinstal; ?></td>
					<td>
						<input type="text" name="hellaspay_maxinstal" value="<?php echo $hellaspay_maxinstal; ?>" />
                        <?php echo $text_instalments; ?>
					</td>
				</tr>
		<tr>
          <td><?php echo $entry_orderurl; ?></td>
          <td>
          <textarea name="hellaspay_orderurl" cols="40" rows="5"><?php echo $hellaspay_orderurl; ?></textarea>
          </td>
        </tr> 
        
		<tr>
          <td><?php echo $entry_url; ?></td>
          <td>
          <textarea name="hellaspay_url" cols="40" rows="5"><?php echo $hellaspay_url; ?></textarea>
          </td>
        </tr> 
        <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" name="hellaspay_total" value="<?php echo $hellaspay_total; ?>" /></td>
          </tr>

		<!-- New statuses... -->
		<tr>
          <td><?php echo $entry_processed_status; ?></td>
          <td><select name="hellaspay_processed_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $hellaspay_processed_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>

        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="hellaspay_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $hellaspay_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="hellaspay_status">
              <?php if ($hellaspay_status) { ?>
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
          <td><input type="text" name="hellaspay_sort_order" value="<?php echo $hellaspay_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>
</div>
<?php echo $footer; ?>