const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config(); // Load environment variables

const app = express();
const port = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Database connection
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'dev',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'anime_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// API Endpoint untuk rekomendasi anime
app.get('/api/recommendations', async (req, res) => {
    try {
        const [rows] = await pool.query(`
            SELECT * FROM anime 
            ORDER BY rating DESC 
            LIMIT 10
        `);
        res.json(rows);
    } catch (err) {
        console.error(err);
        res.status(500).send('Server Error');
    }
});

// API Endpoint untuk streaming info
app.get('/api/stream/:animeId', async (req, res) => {
    const { animeId } = req.params;
    try {
        const [rows] = await pool.query(`
            SELECT video_url FROM anime
            WHERE id = ?
        `, [animeId]);
        
        if (rows.length === 0) {
            return res.status(404).json({ message: 'Anime not found' });
        }
        
        res.json(rows[0]);
    } catch (err) {
        console.error(err);
        res.status(500).send('Server Error');
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Node.js API server running on http://localhost:${port}`);
});