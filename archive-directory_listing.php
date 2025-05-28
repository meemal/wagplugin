<?php
get_header();
?>

<form id="directory-filter-form">
    <input type="text" name="search" placeholder="Search Listings" />

    <select name="sector">
        <option value="">All Sectors</option>
        <?php
        $sectors = get_terms([
            'taxonomy' => 'category',
            'hide_empty' => false,
        ]);
        foreach ($sectors as $sector) {
            echo '<option value="' . esc_attr($sector->slug) . '">' . esc_html($sector->name) . '</option>';
        }
        ?>
    </select>

    <select name="skills">
        <option value="">All Skills</option>
        <?php
        $skills = get_terms([
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ]);
        foreach ($skills as $skill) {
            echo '<option value="' . esc_attr($skill->slug) . '">' . esc_html($skill->name) . '</option>';
        }
        ?>
    </select>

    <button type="submit">Filter</button>
</form>

<div id="directory-listings">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            get_template_part('template-parts/content', 'directory_listing');
        endwhile;
    else :
        echo '<p>No listings found.</p>';
    endif;
    ?>
</div>

<?php
get_footer();
?>
