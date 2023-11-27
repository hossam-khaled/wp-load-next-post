<?php
/**
 * Plugin Name:loade next post
 * Author: hossam khaled
 * Description:loade the next post in the same page
 * Version: 1.0.0
 * Requires PHP: 5.6
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', 'header_ddd');
function header_ddd()
{

    $post = get_post();
    $post_cat = get_the_category($post->ID);
    $data = collection_data_json($post_cat[0]->term_id, $post->ID);
    $data_permalink = collection_order_permalink($data);

?>
    <script type="text/javascript">
        let pid = "<?php echo $post->ID ?>";
        let post_details = <?php echo $data ?>;
        let posts_link = <?php echo $data_permalink ?>;
    </script>
<?php
}

add_action('wp_footer', 'qpanext_postrts_ajax');
function qpanext_postrts_ajax()
{
?>
    <script>
        jQuery(document).ready(function($) {
            let postNum = 1;

            function set_first_url($num = 1) {
                $("body").find("#url-identifier").detach();
                $("body").append($("<input>").attr("type", "hidden").attr("id", "url-identifier").attr("data-url", posts_link[$num]));
            }
            set_first_url();

            function set_next_url(position) {
                if (postNum >= Object.keys(posts_link).length) {
                    set_first_url(0);
                    postNum = 0;
                } else {
                    set_first_url(position);
                }
            }

            function append_next_article(article) {
                $("div#single").append(article);
            }

            function crawl_url(url) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(data) {
                        let article = $(data).find("#single #primary");
                        // console.log(article );
                        append_next_article(article);
                        postNum++;
                        set_next_url(postNum);
                    },
                    error: function(errMsg) {
                        alert(errMsg);
                        return false;
                    }
                });
            }

            $(document).scroll(function(e) {
                if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
                    let next_url = $("body").find("#url-identifier").data("url");
                    if (typeof next_url === "undefined")
                        return false;

                    crawl_url(next_url);
                    window.history.pushState({}, '', next_url);
                }
            });

        });
    </script>

<?php
}

function collection_order_permalink($collection)
{
    $collection = json_decode($collection);
    for ($i = 0; $i < count($collection); $i++) {
        $collection[$i] = get_permalink($collection[$i]->ID);
    }
    return json_encode($collection, JSON_PRETTY_PRINT);
}

function collection_data_json($cat, $post_id = null)
{
    $args = [
      'post_type' => 'post',
      'posts_per_page' => -1,
      'cat' => $cat,
      'orderby' => "date"
    ];
    $allCategoryPosts = new WP_Query($args);
    $allCategoryPosts = $allCategoryPosts->posts;

    return json_encode($allCategoryPosts, JSON_PRETTY_PRINT);
}
