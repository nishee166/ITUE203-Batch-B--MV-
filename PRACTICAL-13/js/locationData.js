/* ==========================================================
   locationData.js
   Static Country -> State -> City data used for the
   dependent dropdown extension (Intermediate Extension).
   ========================================================== */

const locationData = {
    "India": {
        "Gujarat": ["Anand", "Ahmedabad", "Vadodara", "Surat", "Rajkot"],
        "Maharashtra": ["Mumbai", "Pune", "Nagpur"],
        "Karnataka": ["Bengaluru", "Mysuru"],
        "Rajasthan": ["Jaipur", "Udaipur"],
        "Delhi": ["New Delhi"]
    },
    "USA": {
        "California": ["Los Angeles", "San Francisco"],
        "Texas": ["Houston", "Austin"]
    }
};

export default locationData;
