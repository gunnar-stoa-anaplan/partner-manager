import p5 from 'p5'
import 'vanta/src/vanta.topology'

function cssColorToHex(cssValue) {
    const el = document.createElement('div');
    el.style.color = cssValue;
    document.body.appendChild(el);
    const computed = getComputedStyle(el).color;
    document.body.removeChild(el);

    const match = computed.match(/\d+/g);
    if (match) {
        return (parseInt(match[0]) << 16) | (parseInt(match[1]) << 8) | parseInt(match[2]);
    }
    return null;
}

const styles = getComputedStyle(document.documentElement);
const primary = styles.getPropertyValue('--bs-primary').trim();
const primaryHex = primary ? cssColorToHex(primary) : 0xfd3995;

VANTA.TOPOLOGY({
    el: "#net",
    mouseControls: false,
    touchControls: false,
    gyroControls: false,
    color: primaryHex,
    backgroundColor: 0x000000,
    scale: 0.75,
    scaleMobile: 0.50,
    p5: p5
});
