<?php
if (!function_exists('dd')) {
    function dd($data)
    {
        ini_set("highlight.comment", "#969896; font-style: italic");
        ini_set("highlight.default", "#FFFFFF");
        ini_set("highlight.html", "#D16568");
        ini_set("highlight.keyword", "#7FA3BC; font-weight: bold");
        ini_set("highlight.string", "#F2C47E");
        $output = highlight_string("<?php\n\n" . var_export($data, true), true);
        echo "<div style=\"background-color: #1C1E21; padding: 1rem\">{$output}</div>";
        die();
    }
}

    








// Section
$configuration = pip_layout_configuration();
$css_vars      = acf_maybe_get( $configuration, 'css_vars' );

// Fields
$section_intro = get_sub_field( 'section_intro' );
$key_api = get_sub_field('key_api');
$section_end   = get_sub_field( 'section_end' );
$ville = get_sub_field('ville');

$request = wp_remote_get('http://api.weatherstack.com/current',
    array(
        'body' => array(
            'access_key' => $key_api,
            'query' => $ville,
        ),
    )
);

$body = wp_remote_retrieve_body($request);
$api_response = json_decode($body, true);
// dd($body);
// dd($api_response["location"]);


// Configuration
$advanced_mode   = get_sub_field( 'advanced_mode' );
$container_width = get_sub_field( 'container_width' );

// Content width
$content_width = pip_get_responsive_class( $container_width, $advanced_mode );
?>
<section <?php echo acf_maybe_get( $configuration, 'section_id' ); ?>
    class="<?php echo acf_maybe_get( $configuration, 'section_class' ); ?>"
    style="<?php echo apply_filters( 'pip/layout/section_style', '', $configuration ); ?>"
    <?php echo apply_filters( 'pip/layout/section_attributes', '', $configuration ); ?>>

    <?php // To add dynamic markup at the beginning of this layout
    do_action( 'pip/layout/section_start', $configuration ); ?>

    <div class="container">
        <div class="mx-auto <?php echo $content_width; ?>">

            <?php
            // Intro
            if ( $section_intro ) : ?>
                <div class="section_intro <?php echo acf_maybe_get( $css_vars, 'section_intro' ); ?>">
                    <?php echo $section_intro; ?>
                </div>
            <?php endif; ?>


            <?php
                if($key_api || $ville ) : ?>
                <div class="weather">
                    <div class="weather__header">
                        <img src="<?php echo $api_response["current"]["weather_icons"][0] ?>">
                        <p><?php echo $api_response["current"]["weather_descriptions"][0] ?></p>
                        <p>Il fait <?php echo $api_response["current"]["temperature"] ?> °C</p>
                    </div>
                    <div class="weather__containt">
                        <?php echo $api_response["location"]["name"] ?>
                        <?php echo $api_response["location"]["country"] ?>
                        <?php echo $api_response["location"]["region"] ?>
                    </div>
                </div>
                
            <?php endif; ?>

            <?php 
            // Outro
            if ( $section_end ) : ?>
                <div class="section_end <?php echo acf_maybe_get( $css_vars, 'section_end' ); ?>">
                    <?php echo $section_end; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php // To add dynamic markup at the end of this layout
    do_action( 'pip/layout/section_end', $configuration ); ?>

</section>
<?php
