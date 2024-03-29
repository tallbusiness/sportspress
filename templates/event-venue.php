<?php
/**
 * Event Venue
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! isset( $id ) )
	$id = get_the_ID();

$venues = get_the_terms( $id, 'sp_venue' );

if ( ! $venues )
	return;

$show_maps = get_option( 'sportspress_event_show_maps', 'yes' ) == 'yes' ? true : false;
$link_venues = get_option( 'sportspress_event_link_venues', 'no' ) == 'yes' ? true : false;

foreach( $venues as $venue ):
	$t_id = $venue->term_id;
	$meta = get_option( "taxonomy_$t_id" );

	$name = $venue->name;
	if ( $link_venues )
		$name = '<a href="' . get_term_link( $t_id, 'sp_venue' ) . '">' . $name . '</a>';

	$address = sp_array_value( $meta, 'sp_address', '' );
	$latitude = sp_array_value( $meta, 'sp_latitude', 0 );
	$longitude = sp_array_value( $meta, 'sp_longitude', 0 );
	?>
	<h3><?php _e( 'Venue', 'sportspress' ); ?></h3>
	<table class="sp-data-table sp-event-venue">
		<thead>
			<tr>
				<th><?php echo $name; ?></th>
			</tr>
		</thead>
		<?php if ( $address != null || ( $show_maps && $latitude != null && $longitude != null ) ): ?>
			<tbody>
				<tr>
					<td><?php echo $address; ?></td>
				</tr>
				<?php if ( $show_maps && $latitude != null && $longitude != null ): ?>
					<tr>
						<td><?php sp_get_template( 'venue-map.php', array( 'meta' => $meta ) ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		<?php endif; ?>
	</table>
	<?php
endforeach;
