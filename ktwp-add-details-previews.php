<?php
/*
 * Plugin Name: KupieTools Add Preview To Details Elements
 * Plugin URI:        https://michaelkupietz.com/
 * Description:       Adds excerpt summarizing contents of closed Details disclosure elements.
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Michael Kupietz
 * Author URI:        https://michaelkupietz.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://michaelkupietz.com/my-plugin/kupietools-add-details-previews/
 * Text Domain:       mk-plugin
 * Domain Path:       /languages
 */


/**
 * Your code goes below.
 */

$default_selectors='.entry-content > details, .postlistsubdetail';
$avoid_selectors='.fromblog,.bluetext';
// Register settings
    add_action('admin_init', function() {
        register_setting('ktwp_details_excerpt', 'ktwp_details_excerpt_selectors');
        register_setting('ktwp_details_excerpt', 'ktwp_details_excerpt_avoid'); /* This was missing. I added it because it seems like this must be present to save values. Remove again and troubleshoot if it causes problems */
    });
    



add_action('admin_menu', function () {
    global $menu;
    $exists = false;
    
    if ($menu) {
        foreach($menu as $item) {
            if (isset($item[0]) && $item[0] === 'KupieTools') {
                $exists = true;
                break;
            }
        }
    }
    
    if (!$exists) {
        add_menu_page(
            'KupieTools Settings',
            'KupieTools',
            'manage_options',
            'kupietools',
            function() {
                echo '<div class="wrap"><h1>KupieTools</h1>';
                do_action('kupietools_sections');
                echo '</div>';
            },
            'dashicons-admin-tools'
        );
    }
});


// Add THIS plugin's section
    add_action('kupietools_sections', function() {
    global $default_selectors;
    global $avoid_selectors;
        ?>
        <details class="card ktwpdetailsexcerpt" style="max-width: 800px; padding: 20px; margin-top: 20px;" open="true">
            <summary style="font-weight:bold;">KTWP Details Excerpt Selectors</summary>
            Show excerpts of contents for closed Details disclosure elements with the following selector:
            <form method="post" action="options.php">
                <?php
                settings_fields('ktwp_details_excerpt');
                ?>
                <div>
                    <p>
                        <label>
							<strong>Selector of Details elements to add excerpts to:</strong><br>
							<textarea rows="4" name="ktwp_details_excerpt_selectors" style="width: 100%; margin-top: 10px;"><?php echo esc_attr(get_option('ktwp_details_excerpt_selectors', $default_selectors)); ?></textarea> 
                           
                        </label>
                    </p>
                   <p>
                        <label>
							<strong>Selector of elements not to include in preview summaries:</strong><br>
							<textarea rows="4" name="ktwp_details_excerpt_avoid" style="width: 100%; margin-top: 10px;"><?php echo esc_attr(get_option('ktwp_details_excerpt_avoid', $avoid_selectors)); ?></textarea> 
                           
                        </label>
                    </p>
                   
                </div>
                <?php submit_button('Save Settings'); ?>
            </form>
        </details>
        <?php
    });
//}); 

$selectors = get_option('ktwp_details_excerpt_selectors',$default_selectors);
$avoid = get_option('ktwp_details_excerpt_avoid',$avoid_selectors);


function addStyles() {
?>
<style id="ktwp-details-excerpt-style">
details:not([open]) > summary > .detailspreview { display: block;overflow-x: clip;font-size: .8em;line-height: 1.2em;text-overflow: ellipsis;white-space: nowrap; }
details[open] > summary > .detailspreview {display: none;}
</style>
<?php
}
  add_action('wp_footer', 'addStyles'); 
function addDetailsPreview() {
global $selectors;
global $avoid;
    ?>   <script id="ktwp-details-excerpt">
        document.querySelectorAll('<?php echo $selectors; ?>').forEach(details => {
            // Get the summary element
            const summary = details.querySelectorAll('summary<?php echo ($avoid==''?'':(','.$avoid)); ?>');
            if (!summary) return;

            // Get all text content, excluding the summary
            let allText = Array.from(details.childNodes)
               // .filter(node => node !== summary) // Exclude summary
                 .filter(node => !Array.from(summary).includes(node))
            .map(node => node.textContent || '')
                .join(' ')
                .trim();

            // If there's text content, create and append the preview
            if (allText) {
                const preview = document.createElement('span');
                preview.className = 'detailspreview';
                // Limit to 250 characters and add ellipsis
                preview.textContent = (allText.length > 250 ? 
                    allText.substring(0, 250) + '...' : 
                    allText);
                 details.querySelector('summary').appendChild(preview);
            }
        });
    </script>
<?php 
  
}

function xaddDetailsPreview() { /* this one only used <p> text in summary. add previews to <details> elements. */
global $selectors;
	?>   <script>
		document.querySelectorAll('<?php echo $selectors; ?>').forEach(details => {
    const paragraphs = details.querySelectorAll('p');
    if (paragraphs.length === 0) return;

    let combinedText = '';
    let i = 0;

    while (i < paragraphs.length && combinedText.length < 50) {
        combinedText += (combinedText ? ' ' : '') + (paragraphs[i].textContent || '');
        i++;
    }

    if (combinedText.trim()) {
        const preview = document.createElement('span');
        preview.className = 'detailspreview';
        preview.textContent = combinedText.substring(0,250) + '...';
        details.querySelector('summary').appendChild(preview);
    }
});

		</script>
<?php 
	 /* just for interest: this would grap all the text, not just the first paragraph. <script>
    console.log('Script running');
    const details = document.querySelectorAll('.entry-content > details');
    console.log('Found details elements:', details.length);
    
    details.forEach(details => {
        console.log('Processing details:', details);
        const summary = details.querySelector('summary');
        console.log('Summary:', summary);
        const summaryText = summary.textContent;
        console.log('Summary text:', summaryText);
        const fullText = details.textContent.replace(summaryText, '').trim();
        console.log('Full text:', fullText);
        
        if (fullText) {
            const preview = document.createElement('span');
            preview.className = 'detailspreview';
            preview.textContent = fullText;
            summary.appendChild(preview);
        }
    });
    </script> */
	
}
add_action('wp_footer', 'addDetailsPreview');