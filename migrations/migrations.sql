CREATE TABLE servers (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	server_name TEXT,
	host TEXT,
	machine_address TEXT,
	player_count INTEGER,
	player_limit INTEGER,
	server_port INTEGER,
	server_motd_preview TEXT,
	server_motd_content TEXT,
	custom_password INTEGER,
	ttl INTEGER,
	authorization_thing TEXT
);
