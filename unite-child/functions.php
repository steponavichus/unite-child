<?php

// Подключение стилей родительской темы
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'unite', get_template_directory_uri() . '/style.css', array( 'unite-bootstrap', 'unite-icons' ) );
}

// Вывод на главной только тип постов "Недвижимость"
add_action('pre_get_posts', 'my_pre_get_posts');
function my_pre_get_posts( $query ) {
	if( is_admin() ) {
		return $query;
	}

	if ( $query->is_home() &&
		 $query->is_main_query() &&
	   	 $query->query[ 'post_type' ] === NULL
	 ) {
		$query->set( 'post_type', 'realty' );
        if( isset( $_GET[ 'agencyid' ] ) ) {
			$query->set( 'meta_key', 'agencyid' );
			$query->set( 'meta_value', '"' . $_GET["agencyid"] . '"' );
			$query->set( 'meta_compare', 'LIKE' );
    	}
	}

	return $query;
}

// Виджет "Фильтр по агентствам"
add_action( 'widgets_init', function(){
	register_widget( 'AgencyFilter' );
});

class AgencyFilter extends WP_Widget {
	public function __construct() {
        $widget_ops = array(
    		'classname' => 'AgencyFilter',
    		'description' => 'Agency filter widget',
    	);
    	parent::__construct( 'AgencyFilter', 'Agency filter widget', $widget_ops );
    }

	// Вывод виджета на фронтенд
	public function widget( $args, $instance ) {
        echo $args['before_widget'];
    	if ( ! empty( $instance['title'] ) ) {
    		echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    	}
		$posts_per_page = $instance['posts_per_page'];
		$q = new WP_Query("posts_per_page=-1&post_type=agency&order=ASC");
				if( $q->have_posts() ):
					?><ul><?php
					while( $q->have_posts() ): $q->the_post();
						?><li><a href="?agencyid=<?php the_ID() ?>"><?php the_title() ?></a></li><?php
					endwhile;
					?></ul><?php
				endif;
				wp_reset_postdata();
    	echo $args['after_widget'];
    }

	// Вывод формы виджета в ПУ
	public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Title', 'text_domain' );
    	?>
    	<p>
    	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Заголовок:</label>

    	<input
    		class="widefat"
    		id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
    		name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
    		type="text"
    		value="<?php echo esc_attr( $title ); ?>">
    	</p>
    	<?php
    }

	// Сохранение формы в ПУ
	public function update( $new_instance, $old_instance ) {
        $instance = array();
    	$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    	return $instance;
    }
}
