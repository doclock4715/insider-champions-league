import axios from 'axios';

const API_URL = 'http://localhost/api'; // Laravel API adresi

export const leagueService = {
    getStatus() {
        return axios.get(`${API_URL}/league-status`);
    },
    playNextWeek() {
        return axios.post(`${API_URL}/play-week`);
    }
};