document.addEventListener("DOMContentLoaded", () => {
    const handle = document.getElementById("handle");
    const machine = document.getElementById("machine-container");
    const fallingCapsule = document.getElementById("falling-capsule");
    const overlay = document.getElementById("overlay");
    const zoomCapsule = document.getElementById("zoom-capsule");
    const openCapsule = document.getElementById("open-capsule");
    const resultText = document.getElementById("result-text");
    const instruction = document.getElementById("instruction-text");

    let isPlaying = false;

    handle.addEventListener("click", () => {
        if(isPlaying) return;
        isPlaying = true;
        
        // ハンドルのテキストを非表示
        instruction.classList.add("hidden");
        
        // 1. ハンドル回転とマシン振動
        handle.classList.add("anim-turn-handle");
        machine.classList.add("anim-shake-machine");

        // 2. 約0.8秒後にカプセル排出
        setTimeout(() => {
            fallingCapsule.classList.remove("hidden");
            fallingCapsule.classList.add("anim-drop-capsule");
        }, 800);

        // 3. カプセル排出後、ズームアップ画面へ切り替え
        setTimeout(() => {
            machine.classList.remove("anim-shake-machine");
            overlay.classList.add("visible");
            zoomCapsule.classList.add("anim-shake-zoom");
        }, 2200);

        // 4. カプセル振動後、オープン！
        setTimeout(() => {
            zoomCapsule.classList.add("anim-scale-out");
            
            // 開いたカプセルの表示
            setTimeout(() => {
                zoomCapsule.classList.add("hidden");
                openCapsule.classList.remove("hidden");
                openCapsule.classList.add("anim-pop-in");
                
                // 結果テキストの表示
                resultText.classList.remove("hidden");
                resultText.classList.add("anim-pop-in");
            }, 300);

        }, 4000);
        
    });
});
