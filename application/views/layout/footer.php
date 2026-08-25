</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
	var sidebar = document.getElementById('appSidebar');
	var backdrop = document.getElementById('sidebarBackdrop');
	var toggle = document.getElementById('sidebarToggle');
	if (!sidebar || !backdrop || !toggle) return;

	function closeSidebar() { sidebar.classList.remove('show'); backdrop.classList.remove('show'); }
	function openSidebar() { sidebar.classList.add('show'); backdrop.classList.add('show'); }

	toggle.addEventListener('click', function () {
		sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
	});
	backdrop.addEventListener('click', closeSidebar);
	sidebar.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeSidebar); });
})();
</script>
</body>
</html>
