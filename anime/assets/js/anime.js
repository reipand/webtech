const express = require('express');
const router = express.Router();
const pool = require('../server').pool;

fetch('https://api.myanimelist.net/v2/anime?q=Naruto', {
    headers: {
        'X-MAL-CLIENT-ID': 'your_client_id'
    }
})
.then(response => response.json())
.then(data => console.log(data));

// Get all anime
router.get('/', async (req, res) => {
    const [rows] = await pool.query('SELECT * FROM anime');
    res.json(rows);
});

// Add anime
router.post('/', async (req, res) => {
    const { title, year, genre, studio, description } = req.body;
    const [result] = await pool.query(
        'INSERT INTO anime (title, year, genre, studio, description) VALUES (?, ?, ?, ?, ?)',
        [title, year, genre, studio, description]
    );
    res.status(201).json({ id: result.insertId });
});

// Delete anime
router.delete('/:id', async (req, res) => {
    const { id } = req.params;
    await pool.query('DELETE FROM anime WHERE id = ?', [id]);
    res.status(204).send();
});

module.exports = router;