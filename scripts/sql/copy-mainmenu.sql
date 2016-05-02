INSERT INTO mainmenu (
	texti,
	target,
	gerd,
	href,
	ord,
	division,
	level,
	link,
	shortcut
)
SELECT 
	texti,
	target,
	gerd,
	REPLACE(href, 'is', 'auka'),
	ord,
	3,
	level,
	REPLACE(link, 'is', 'auka'),
	shortcut
FROM
	mainmenu
WHERE
	division = 2;

