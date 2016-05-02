insert into gallery_album 
    (name, codename, description, folder, 
    pager, cover, tags, insertedAt, updatedAt,
    insertedBy, updatedBy, active, newwindow,
    sizeLarge, sizeMedium, sizeThumb, ordType) 
select 
    concat(name,'-en'), concat(codename,'-en'), description, 
    concat(folder,'-en'), pager, cover, tags, insertedAt,
    updatedAt, insertedBy, updatedBy, active, newwindow, 
    sizeLarge, sizeMedium, sizeThumb, ordType from gallery_album where id = 37;



insert into gallery_image 
    (albumId, name, codename, description, 
    tags, potd, ord, filename, mime, insertedAt,
    updatedAt, insertedBy, updatedBy, active) 
select 
    46, name, concat(codename,'-en'), description, tags,
    potd, ord, filename, mime, insertedAt, updatedAt,
    insertedBy, updatedBy, active from gallery_image where albumId = 37;



