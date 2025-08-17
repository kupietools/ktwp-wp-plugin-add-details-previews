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
details:not([open]) > summary > .detailspreview { display: block;overflow-x: clip;font-size: .8em;line-height: 1.2em;text-overflow: ellipsis; /* white-space: nowrap; */ /* note: nowrap removed and next 3 added because was causing lines that didn't break to be wider than the screen */ word-break: normal;overflow-wrap: break-word;hyphens: manual;}
details[open] > summary > .detailspreview {display: none;}
</style>
<?php
}
  add_action('wp_footer', 'addStyles'); 

function addDetailsPreview() {
global $selectors;
global $avoid;
    ?>   <script id="ktwp-details-excerpt">
        const lastTextContentMap = new WeakMap();

        function extractTextContent(details) {
            const detailsClone = details.cloneNode(true);
            const summaryClone = detailsClone.querySelector('summary');
            if (summaryClone) summaryClone.remove();
            
            // Remove scripts and styles
            detailsClone.querySelectorAll('script, style<?php echo ($avoid ? ', ' . $avoid : ''); ?>').forEach(el => el.remove());
            
            return (detailsClone.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function generatePreviewForDetails(details) {
            const summary = details.querySelector('summary');
            if (!summary) return;

            // Remove existing preview
            const existingPreview = summary.querySelector('.detailspreview');
            if (existingPreview) {
                existingPreview.remove();
            }

            const allText = extractTextContent(details);

            if (allText) {
                const preview = document.createElement('span');
                preview.className = 'detailspreview';
                preview.textContent = (allText.length > 250 ? 
                    allText.substring(0, 247) + '...' : 
                    allText);
                summary.appendChild(preview);
            }

            // Store current text content for comparison
            lastTextContentMap.set(details, allText);
        }

        function setupDetailsObserver(details) {
            if (details.dataset.observerAttached) return;
            
            generatePreviewForDetails(details);
            
            let timeoutId = null;
            const observer = new MutationObserver(() => {
                if (timeoutId) clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    // Check if text content actually changed
                    const currentText = extractTextContent(details);
                    const lastText = lastTextContentMap.get(details) || '';
                    
                    if (currentText !== lastText) {
                        generatePreviewForDetails(details);
                    }
                }, 100); // Debounce to run at most once per 100ms
            });

            observer.observe(details, {
                childList: true,
                subtree: true,
                characterData: true
            });

            details.dataset.observerAttached = 'true';
        }

        // Setup observers for existing details elements
        document.querySelectorAll('<?php echo $selectors; ?>').forEach(setupDetailsObserver);

        // Watch for new details elements
        const pageObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.matches && node.matches('<?php echo $selectors; ?>')) {
                            setupDetailsObserver(node);
                        }
                        node.querySelectorAll && node.querySelectorAll('<?php echo $selectors; ?>').forEach(setupDetailsObserver);
                    }
                });
            });
        });

        pageObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
<?php 
}


add_action('wp_footer', 'addDetailsPreview');