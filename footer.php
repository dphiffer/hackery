		<footer class="dark">
			<div class="links">
				Find me on
				<?php
				
				wp_list_bookmarks(array(
					'title_li' => '',
					'orderby' => 'rating',
					'order' => 'DESC',
					'categorize' => false
				));
				
				?>
				</ul>
			</div>
			<p class="website-credit">
				Website by <a href="http://phiffer.org/">Dan Phiffer</a> /
				<a href="https://github.com/dphiffer/hackery">Hackery</a> theme
			</p>
			<br class="clear">
		</footer>
		<?php hackery_wp_footer(); ?>
	</body>
</html>
