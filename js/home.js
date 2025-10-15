document.addEventListener("DOMContentLoaded", function () {
    const storyDiv = document.querySelector(".product-story-div");
    const storyTitle = document.querySelector(".story-div h3");
    const storyText = document.querySelector(".story-text");
    const buttons = document.querySelectorAll(".product-story-nav button");

    const stories = {
        labubu: {
            title: "Labubu",
            text: "Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm...\
            Labubu is a mischievous little forest creature with endless energy and charm..."
        },
        twinkle: {
            title: "Twinkle",
            text: "Twinkle lights up the skies with her shimmering personality and magical sparkle..."
        },
        zimomo: {
            title: "Zimomo",
            text: "Zimomo is quiet but wise, always watching over the collection with a caring heart..."
        }
    };

    const backgrounds = {
        labubu: "/PekoPop/static/images/home-story-labubu.jpg",
        twinkle: "/PekoPop/static/images/home-story-twinkle.jpg",
        zimomo: "/PekoPop/static/images/home-story-zimomo.jpg"
    };

    buttons.forEach(button => {
        button.addEventListener("click", () => {
            const character = button.dataset.character;

            // Update content
            storyTitle.textContent = stories[character].title;
            storyText.textContent = stories[character].text;

            // Change background image with fade effect
            storyDiv.style.opacity = 0;
            setTimeout(() => {
                storyDiv.style.backgroundImage = `url(${backgrounds[character]})`;
                storyDiv.style.opacity = 1;
            }, 400); // half of your transition duration

            // Highlight active button
            buttons.forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");
        });
    });

    // Initialize by simulating click on first button
    buttons[0].click();
});