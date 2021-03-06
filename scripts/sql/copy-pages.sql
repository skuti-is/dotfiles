INSERT INTO page (
	page_moduleid,
	page_title,
	page_codename,
	page_content,
	page_author,
	page_division,
	page_visible,
	page_category,
	page_keywords,
	page_style,
	page_password,
	page_createdAt,
	page_updatedAt,
	page_updatedBy,
	page_views,
	page_hide_right
)
SELECT 
	page_moduleid,
	page_title,
	concat(page_codename, '-en'),
	page_content,
	page_author,
	3,
	page_visible,
	page_category,
	page_keywords,
	page_style,
	page_password,
	unix_timestamp(),
	0,
	1,
	0,
	page_hide_right
FROM
	page
WHERE
	page_division = 2;
