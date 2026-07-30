CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(120) NOT NULL,
    contact_name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'lead',
    tags VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    KEY clients_status_index (status),
    KEY clients_company_index (company)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clients (company, contact_name, email, phone, status, tags, notes, created_at) VALUES
    ('Nordwind Logistics', 'Elena Marinescu', 'elena@nordwind.example', '+40 721 114 552', 'active',  'logistics,enterprise', 'Renewed the yearly contract in June.', '2026-01-14 09:20:00'),
    ('Cortex Analytics',   'Radu Dobre',      'radu@cortex.example',   '+40 733 908 217', 'active',  'saas,analytics',      'Asked for a second reporting seat.',   '2026-02-03 11:05:00'),
    ('Blueharbor Retail',  'Ioana Preda',     'ioana@blueharbor.example', '+40 745 330 118', 'lead',  'retail',              'Waiting for their budget approval.',   '2026-03-22 15:40:00'),
    ('Verdant Farms',      'Mihai Toma',      'mihai@verdant.example', NULL,              'lead',    'agriculture,smb',     'Met at the Cluj trade fair.',          '2026-04-08 08:15:00'),
    ('Helix Medical',      'Andreea Voicu',   'andreea@helix.example', '+40 726 441 903', 'active',  'healthcare,enterprise', 'Quarterly review scheduled.',        '2026-05-19 13:30:00'),
    ('Pixelforge Studio',  'Cristian Ilie',   'cristian@pixelforge.example', '+40 751 209 664', 'churned', 'agency',        'Left for a cheaper competitor.',       '2025-11-27 16:50:00');
