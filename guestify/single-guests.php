<?php
/**
 * Single template for 'guests' custom post type
 * WordPress automatically uses this for the 'guests' post type
 * Based on Bob Diamond Speaker layout design
 * 
 * PHASE 8: Uses native WordPress post_meta only - NO Pods dependency
 * 
 * @package Guestify
 */

$post_id = get_the_ID();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title(); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php wp_head(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .hero-section {
            background: linear-gradient(135deg, #1c0d5a 0%, #2a1b6b 50%, #295cff 100%);
            color: white;
            padding: 60px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            pointer-events: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 80px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            animation: slideInLeft 1s ease-out;
        }

        .hero-image {
            animation: slideInRight 1s ease-out;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .hero-text h4 {
            color: #295cff;
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }

        .hero-text h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #295cff, transparent);
        }

        .hero-text h1 {
            font-size: 4rem;
            margin-bottom: 30px;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #295cff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }

        .hero-text .professional-title {
            color: #295cff;
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-style: italic;
        }

        .hero-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .hero-image {
            text-align: center;
            position: relative;
        }

        .hero-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: linear-gradient(45deg, #295cff, #1c0d5a);
            border-radius: 20px;
            opacity: 0.1;
            z-index: -1;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            transition: transform 0.3s ease;
        }

        .hero-image:hover img {
            transform: translateY(-10px);
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #295cff, #1e4bcc);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(41, 92, 255, 0.4);
        }

        .social-links {
            background: white;
            border-radius: 50px;
            padding: 20px 40px;
            margin: 40px auto 0;
            box-shadow: 0 25px 40px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            gap: 15px;
            max-width: fit-content;
            position: relative;
            z-index: 10;
            transform: translateY(-30px);
        }

        .social-links a {
            color: #666;
            font-size: 35px;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .social-links a:hover { transform: translateY(-3px); }
        .social-links .fa-linkedin { color: #0A66C2; }
        .social-links .fa-facebook-square { color: #0866FF; }
        .social-links .fa-instagram { color: #262626; }
        .social-links .fa-tiktok { color: #000000; }
        .social-links .fa-youtube { color: #CD201F; }
        .social-links .fa-x-twitter { color: #000000; }
        .social-links .fa-pinterest { color: #BD081C; }
        .social-links .fa-link { color: #295cff; }

        .content-section {
            padding: 80px 20px;
            background: #f8f9fa;
        }

        .topics-section {
            text-align: center;
            margin-bottom: 60px;
        }

        .topics-section h2 {
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #1c0d5a;
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .topic-card {
            background: white;
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .topic-card:hover { transform: translateY(-5px); }

        .topic-card .icon {
            width: 60px;
            height: 60px;
            background: #295cff;
            border-radius: 10px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .topic-card h3 { color: #1c0d5a; margin-bottom: 10px; }
        .topic-card p { color: #666; font-size: 0.9rem; }

        .questions-section h2 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 40px;
            color: #1c0d5a;
        }

        .questions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .question-card {
            background: #1c0d5a;
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .question-card h4 { color: #295cff; margin-bottom: 15px; }

        .biography-section {
            background: white;
            padding: 80px 20px;
        }

        .bio-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 60px;
            align-items: start;
        }

        .bio-image { text-align: center; }
        .bio-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .bio-text h2 { color: #1c0d5a; margin-bottom: 30px; font-size: 2.5rem; }
        .bio-text h3 { color: #295cff; margin-bottom: 20px; }
        .bio-text p { margin-bottom: 20px; line-height: 1.8; color: #666; }

        @media (max-width: 768px) {
            .hero-content { grid-template-columns: 1fr; text-align: center; gap: 40px; }
            .hero-text h1 { font-size: 2.5rem; }
            .social-links { flex-wrap: wrap; padding: 15px 20px; gap: 10px; }
            .social-links a { font-size: 25px; }
            .bio-content { grid-template-columns: 1fr; gap: 40px; }
            .topics-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
while (have_posts()) :
    the_post();
    
    // Batch-load ALL post_meta in a single query (saves ~30 individual DB queries)
    $meta = get_post_meta($post_id);
    $m = function($key) use ($meta) {
        return isset($meta[$key][0]) ? $meta[$key][0] : '';
    };

    // Core fields
    $first_name = $m('first_name');
    $last_name = $m('last_name');
    $full_name = $m('full_name');
    $guest_title = $m('guest_title');
    $company = $m('company');

    // Determine guest name to display
    $guest_name = $full_name ?: trim($first_name . ' ' . $last_name) ?: get_the_title();

    // Messaging fields
    $introduction = $m('introduction') ?: get_the_content();
    $biography = $m('biography') ?: $m('biography_long');

    // Images - get attachment IDs and convert to URLs
    $guest_headshot_id = $m('guest_headshot');
    $vertical_image_id = $m('vertical_image');
    $horizontal_image_id = $m('horizontal_image');

    $guest_headshot = $guest_headshot_id ? wp_get_attachment_url($guest_headshot_id) : '';
    $vertical_image = $vertical_image_id ? wp_get_attachment_url($vertical_image_id) : '';
    $horizontal_image = $horizontal_image_id ? wp_get_attachment_url($horizontal_image_id) : '';

    // Social media fields
    $facebook = $m('1_facebook');
    $instagram = $m('1_instagram');
    $linkedin = $m('1_linkedin');
    $pinterest = $m('1_pinterest');
    $tiktok = $m('1_tiktok');
    $twitter = $m('1_twitter');
    $youtube = $m('guest_youtube');
    $website1 = $m('1_website');
    $website2 = $m('2_website');

    // Topics (1-5)
    $topics = array();
    for ($i = 1; $i <= 5; $i++) {
        $topic = $m("topic_{$i}");
        if ($topic) $topics[] = $topic;
    }

    // Questions (1-25)
    $questions = array();
    for ($i = 1; $i <= 25; $i++) {
        $question = $m("question_{$i}");
        if ($question) $questions[] = $question;
    }
    ?>

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1><?php echo esc_html($guest_name); ?></h1>
                    
                    <?php if ($guest_title) : ?>
                        <div class="professional-title"><?php echo esc_html($guest_title); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($company) : ?>
                        <div class="professional-title"><?php echo esc_html($company); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($introduction) : ?>
                        <p><?php echo wp_kses_post($introduction); ?></p>
                    <?php endif; ?>
                    
                    <a href="#bio" class="btn">View Bio</a>
                </div>
                
                <div class="hero-image">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large', array('alt' => esc_attr($guest_name))); ?>
                    <?php elseif ($guest_headshot || $vertical_image) : ?>
                        <img src="<?php echo esc_url($guest_headshot ?: $vertical_image); ?>" alt="<?php echo esc_attr($guest_name); ?>">
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($linkedin || $facebook || $instagram || $pinterest || $tiktok || $twitter || $youtube || $website1 || $website2) : ?>
                <div class="social-links">
                    <?php if ($linkedin) : ?><a href="<?php echo esc_url($linkedin); ?>" target="_blank"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                    <?php if ($facebook) : ?><a href="<?php echo esc_url($facebook); ?>" target="_blank"><i class="fab fa-facebook-square"></i></a><?php endif; ?>
                    <?php if ($instagram) : ?><a href="<?php echo esc_url($instagram); ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if ($pinterest) : ?><a href="<?php echo esc_url($pinterest); ?>" target="_blank"><i class="fab fa-pinterest"></i></a><?php endif; ?>
                    <?php if ($tiktok) : ?><a href="<?php echo esc_url($tiktok); ?>" target="_blank"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                    <?php if ($twitter) : ?><a href="<?php echo esc_url($twitter); ?>" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
                    <?php if ($youtube) : ?><a href="<?php echo esc_url($youtube); ?>" target="_blank"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    <?php if ($website1) : ?><a href="<?php echo esc_url($website1); ?>" target="_blank"><i class="fas fa-link"></i></a><?php endif; ?>
                    <?php if ($website2) : ?><a href="<?php echo esc_url($website2); ?>" target="_blank"><i class="fas fa-link"></i></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($topics) || !empty($questions)) : ?>
        <section class="content-section">
            <div class="container">
                <?php if (!empty($topics)) : ?>
                    <div class="topics-section">
                        <h2>Suggested Topics</h2>
                        <div class="topics-grid">
                            <?php foreach ($topics as $index => $topic) : ?>
                                <div class="topic-card">
                                    <div class="icon"><i class="fas fa-comments"></i></div>
                                    <h3>Topic <?php echo $index + 1; ?></h3>
                                    <p><?php echo esc_html($topic); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($questions)) : ?>
                    <div class="questions-section">
                        <h2>Suggested Questions</h2>
                        <div class="questions-grid">
                            <?php foreach ($questions as $index => $question) : ?>
                                <div class="question-card">
                                    <h4>Question <?php echo $index + 1; ?></h4>
                                    <p><?php echo esc_html($question); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <section id="bio" class="biography-section">
        <div class="container">
            <div class="bio-content">
                <div class="bio-image">
                    <?php if ($horizontal_image || $guest_headshot || $vertical_image) : ?>
                        <img src="<?php echo esc_url($horizontal_image ?: $guest_headshot ?: $vertical_image); ?>" alt="<?php echo esc_attr($guest_name); ?>">
                    <?php endif; ?>
                </div>
                <div class="bio-text">
                    <h3>View</h3>
                    <h2>Biography</h2>
                    <?php if ($biography) : ?>
                        <?php echo wp_kses_post($biography); ?>
                    <?php else : ?>
                        <?php the_content(); ?>
                    <?php endif; ?>

                    <?php if ($linkedin || $facebook || $instagram || $pinterest || $tiktok || $twitter || $youtube || $website1 || $website2) : ?>
                        <div class="social-links" style="background: transparent; box-shadow: none; padding: 20px 0; transform: none; margin: 20px 0;">
                            <?php if ($linkedin) : ?><a href="<?php echo esc_url($linkedin); ?>" target="_blank"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                            <?php if ($facebook) : ?><a href="<?php echo esc_url($facebook); ?>" target="_blank"><i class="fab fa-facebook-square"></i></a><?php endif; ?>
                            <?php if ($instagram) : ?><a href="<?php echo esc_url($instagram); ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
                            <?php if ($pinterest) : ?><a href="<?php echo esc_url($pinterest); ?>" target="_blank"><i class="fab fa-pinterest"></i></a><?php endif; ?>
                            <?php if ($tiktok) : ?><a href="<?php echo esc_url($tiktok); ?>" target="_blank"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                            <?php if ($twitter) : ?><a href="<?php echo esc_url($twitter); ?>" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
                            <?php if ($youtube) : ?><a href="<?php echo esc_url($youtube); ?>" target="_blank"><i class="fab fa-youtube"></i></a><?php endif; ?>
                            <?php if ($website1) : ?><a href="<?php echo esc_url($website1); ?>" target="_blank"><i class="fas fa-link"></i></a><?php endif; ?>
                            <?php if ($website2) : ?><a href="<?php echo esc_url($website2); ?>" target="_blank"><i class="fas fa-link"></i></a><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php endwhile; ?>

<script>
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>
