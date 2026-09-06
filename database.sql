CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (full_name, email, password, role)
VALUES (
    'Numukizah Admin',
    'numukizah@gmail.com',
    '$2y$10$wlrZbIbuP61/fYTn9fv.J.B9nzGHnE60XgjqqaYvhv78grhbMt7hC',
    'admin'
)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    password = VALUES(password),
    role = 'admin';

CREATE TABLE chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE course_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    instructions TEXT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

INSERT INTO courses (id, title, description) VALUES
(1, 'Web Development Fundamentals', 'Learn how websites work with HTML, CSS, PHP, and responsible web practices.'),
(2, 'Database Essentials', 'Understand tables, relationships, SQL queries, and safe data management.');

INSERT INTO lessons (course_id, title, content) VALUES
(1, 'HTML Page Structure', 'HTML gives a webpage its structure. Use headings for titles, paragraphs for text, and links for navigation.'),
(1, 'CSS and Presentation', 'CSS controls presentation such as colors, spacing, typography, and responsive layouts.'),
(2, 'Relational Tables', 'A relational database stores related records in tables. Primary keys identify rows, while foreign keys connect tables.');

INSERT INTO course_exercises (course_id, title, instructions) VALUES
(1, 'Build a Profile Page', 'Create a small profile page using one HTML heading, one paragraph, an image, and a CSS rule that changes the heading color. Check that the page works on a narrow screen.'),
(2, 'Design a Student Database', 'Write a table design for students and courses. Choose a primary key for each table and identify the foreign key that connects a student to a course.');

INSERT INTO quizzes (id, course_id, title, description) VALUES
(1, 1, 'Web Development Basics Quiz', 'Check your understanding of HTML, CSS, PHP, and web fundamentals.'),
(2, 2, 'Database Basics Quiz', 'Test your knowledge of tables, keys, and SQL.'),
(3, 1, 'Web Development Practice Quiz', 'Apply your knowledge of page structure, styling, and server-side code.'),
(4, 2, 'Database Practice Quiz', 'Apply your knowledge of relationships, queries, and data integrity.'),
(5, 1, 'ICT Comprehensive Assessment', 'Complete 25 questions covering web development, databases, networking, and cybersecurity.');

INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(1, 'Which language provides the structure of a webpage?', 'CSS', 'HTML', 'SQL', 'PHP', 'B'),
(1, 'Which technology is used primarily to style a webpage?', 'CSS', 'HTML', 'MySQL', 'JSON', 'A'),
(1, 'What does PHP commonly run on?', 'The server', 'The monitor', 'The keyboard', 'The browser cache', 'A'),
(2, 'What uniquely identifies a row in a table?', 'Foreign key', 'Primary key', 'CSS class', 'Query string', 'B'),
(2, 'Which SQL command reads data from a table?', 'INSERT', 'UPDATE', 'SELECT', 'DELETE', 'C'),
(3, 'Which HTML element creates a hyperlink?', 'img', 'a', 'link', 'url', 'B'),
(3, 'Which PHP symbol starts a variable name?', '#', '@', '$', '&', 'C'),
(4, 'Which SQL clause filters rows?', 'ORDER BY', 'WHERE', 'GROUP BY', 'LIMIT', 'B'),
(4, 'What protects a relationship between two tables?', 'Foreign key constraint', 'CSS selector', 'HTML attribute', 'Session cookie', 'A'),
(5, 'Which HTML element contains the main visible content of a page?', 'body', 'head', 'meta', 'title', 'A'),
(5, 'Which CSS property changes the text color?', 'font-style', 'color', 'text-align', 'display', 'B'),
(5, 'Which HTML attribute provides alternative text for an image?', 'src', 'href', 'alt', 'title', 'C'),
(5, 'Which CSS layout system is useful for arranging items in one dimension?', 'Flexbox', 'Cookies', 'Sessions', 'SQL', 'A'),
(5, 'Which JavaScript keyword declares a variable that can be reassigned?', 'const', 'let', 'class', 'return', 'B'),
(5, 'What does PHP use to mark the beginning of a variable name?', '$', '#', '@', '%', 'A'),
(5, 'Which PHP function safely escapes text for HTML output?', 'htmlspecialchars', 'password_hash', 'json_decode', 'trim_all', 'A'),
(5, 'Which PHP function creates a secure password hash?', 'password_verify', 'password_hash', 'hash_password_text', 'encrypt_html', 'B'),
(5, 'Which HTTP method is commonly used to send form data?', 'POST', 'FETCH', 'PUSH', 'SEND', 'A'),
(5, 'What does SQL stand for?', 'Simple Query Language', 'Structured Query Language', 'System Question List', 'Server Queue Logic', 'B'),
(5, 'Which SQL command adds a new record?', 'CREATE', 'INSERT', 'SELECT', 'ALTER', 'B'),
(5, 'Which SQL command changes existing records?', 'UPDATE', 'CHANGE', 'MODIFY TABLE', 'REPLACE ALL', 'A'),
(5, 'Which SQL clause sorts query results?', 'SORT', 'ORDER BY', 'ARRANGE', 'GROUP', 'B'),
(5, 'Which key connects a record to a record in another table?', 'Primary key', 'Foreign key', 'Display key', 'Session key', 'B'),
(5, 'What is the purpose of a database index?', 'To speed up searches', 'To delete rows', 'To style tables', 'To encrypt passwords automatically', 'A'),
(5, 'Which network device forwards traffic between different networks?', 'Switch', 'Router', 'Monitor', 'Keyboard', 'B'),
(5, 'What does IP stand for in networking?', 'Internet Protocol', 'Internal Program', 'Internet Port', 'Input Process', 'A'),
(5, 'Which protocol is commonly used to load secure websites?', 'HTTP', 'FTP', 'HTTPS', 'SMTP', 'C'),
(5, 'What is a firewall used for?', 'Filtering network traffic', 'Writing HTML', 'Storing images', 'Formatting text', 'A'),
(5, 'Which practice helps protect an account?', 'Using the same password everywhere', 'Sharing passwords', 'Using a strong unique password', 'Disabling updates', 'C'),
(5, 'What is phishing?', 'A safe backup method', 'A fraudulent attempt to obtain sensitive information', 'A database command', 'A CSS technique', 'B'),
(5, 'Why should software be updated regularly?', 'To fix security issues and bugs', 'To remove all user accounts', 'To disable backups', 'To reduce screen size', 'A'),
(5, 'What does a backup provide?', 'A copy that can help restore data', 'A faster processor', 'A new password', 'A network cable', 'A'),
(5, 'Which principle gives users only the access they need?', 'Least privilege', 'Open access', 'Shared identity', 'Maximum permission', 'A'),
(5, 'What should you do before running unknown downloaded software?', 'Open it immediately', 'Verify its source and scan it', 'Share it with others', 'Disable security tools', 'B');