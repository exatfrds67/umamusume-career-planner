#!/usr/bin/env python3
"""
OpenCV OCR Preprocessing Pipeline

Advanced image preprocessing for improving OCR accuracy on Uma Musume
game screenshots. Implements adaptive thresholding, deskewing,
noise reduction, and binarization.

Usage:
    python ocr_preprocess.py <input_path> <output_path> [--config <json>]

Output:
    JSON object with processing metadata to stdout
"""

import sys
import json
import os
import time

try:
    import cv2
    import numpy as np
    OPENCV_AVAILABLE = True
except ImportError:
    OPENCV_AVAILABLE = False


def adaptive_threshold(image, block_size=11, c_value=2):
    """Apply adaptive thresholding for varying lighting conditions."""
    if len(image.shape) == 3:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    else:
        gray = image.copy()

    return cv2.adaptiveThreshold(
        gray,
        255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        block_size,
        c_value,
    )


def deskew(image, max_angle=15.0):
    """Detect and correct image skew using Hough line transform."""
    if len(image.shape) == 3:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    else:
        gray = image.copy()

    edges = cv2.Canny(gray, 50, 150, apertureSize=3)
    lines = cv2.HoughLinesP(
        edges, 1, np.pi / 180, threshold=100, minLineLength=100, maxLineGap=10
    )

    if lines is None:
        return image, 0.0

    angles = []
    for line in lines:
        x1, y1, x2, y2 = line[0]
        angle = np.degrees(np.arctan2(y2 - y1, x2 - x1))
        if abs(angle) < max_angle:
            angles.append(angle)

    if not angles:
        return image, 0.0

    median_angle = np.median(angles)

    if abs(median_angle) < 0.5:
        return image, float(median_angle)

    h, w = image.shape[:2]
    center = (w // 2, h // 2)
    rotation_matrix = cv2.getRotationMatrix2D(center, median_angle, 1.0)
    rotated = cv2.warpAffine(
        image, rotation_matrix, (w, h), flags=cv2.INTER_CUBIC,
        borderMode=cv2.BORDER_REPLICATE,
    )

    return rotated, float(median_angle)


def reduce_noise(image, strength=10):
    """Apply Non-Local Means Denoising for superior noise reduction."""
    if len(image.shape) == 3:
        return cv2.fastNlMeansDenoisingColored(image, None, strength, strength, 7, 21)
    return cv2.fastNlMeansDenoising(image, None, strength, 7, 21)


def enhance_contrast(image):
    """Apply CLAHE (Contrast Limited Adaptive Histogram Equalization)."""
    if len(image.shape) == 3:
        lab = cv2.cvtColor(image, cv2.COLOR_BGR2LAB)
        l_channel, a_channel, b_channel = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        l_channel = clahe.apply(l_channel)
        enhanced = cv2.merge([l_channel, a_channel, b_channel])
        return cv2.cvtColor(enhanced, cv2.COLOR_LAB2BGR)
    else:
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        return clahe.apply(image)


def sharpen(image):
    """Apply unsharp masking for text edge enhancement."""
    gaussian = cv2.GaussianBlur(image, (0, 0), 3)
    return cv2.addWeighted(image, 1.5, gaussian, -0.5, 0)


def resize_for_ocr(image, max_width=1920, max_height=1080):
    """Resize image while maintaining aspect ratio for optimal OCR."""
    h, w = image.shape[:2]

    if w <= max_width and h <= max_height:
        return image, 1.0

    scale = min(max_width / w, max_height / h)
    new_w = int(w * scale)
    new_h = int(h * scale)
    resized = cv2.resize(image, (new_w, new_h), interpolation=cv2.INTER_CUBIC)

    return resized, scale


def preprocess_pipeline(input_path, output_path, config=None):
    """
    Run the full OpenCV preprocessing pipeline.

    Returns metadata dict with processing details.
    """
    if not OPENCV_AVAILABLE:
        return {
            "success": False,
            "error": "OpenCV (cv2) is not installed. Install with: pip install opencv-python-headless",
            "opencv_available": False,
        }

    if not os.path.exists(input_path):
        return {
            "success": False,
            "error": f"Input file not found: {input_path}",
        }

    config = config or {}
    start_time = time.time()
    steps_applied = []

    image = cv2.imread(input_path)
    if image is None:
        return {
            "success": False,
            "error": f"Failed to read image: {input_path}",
        }

    original_h, original_w = image.shape[:2]

    # Step 1: Resize if needed
    max_width = config.get("resize_max_width", 1920)
    max_height = config.get("resize_max_height", 1080)
    image, scale = resize_for_ocr(image, max_width, max_height)
    if scale != 1.0:
        steps_applied.append(f"resize (scale={scale:.2f})")

    # Step 2: Deskew
    if config.get("deskew_enabled", True):
        image, skew_angle = deskew(image)
        steps_applied.append(f"deskew (angle={skew_angle:.2f}°)")

    # Step 3: Noise reduction
    denoise_strength = config.get("denoise_strength", 10)
    if denoise_strength > 0:
        image = reduce_noise(image, denoise_strength)
        steps_applied.append(f"denoise (strength={denoise_strength})")

    # Step 4: Contrast enhancement (CLAHE)
    if config.get("contrast_enhancement", True):
        image = enhance_contrast(image)
        steps_applied.append("clahe_contrast")

    # Step 5: Sharpen
    if config.get("sharpen_enabled", True):
        image = sharpen(image)
        steps_applied.append("sharpen")

    # Step 6: Convert to grayscale
    if len(image.shape) == 3:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    else:
        gray = image

    # Step 7: Adaptive thresholding (binarization)
    if config.get("adaptive_threshold", True):
        block_size = config.get("threshold_block_size", 11)
        c_value = config.get("threshold_c_value", 2)
        processed = adaptive_threshold(gray, block_size, c_value)
        steps_applied.append(f"adaptive_threshold (block={block_size}, c={c_value})")
    else:
        processed = gray

    # Save output
    cv2.imwrite(output_path, processed)
    processing_time = time.time() - start_time

    final_h, final_w = processed.shape[:2]

    return {
        "success": True,
        "opencv_available": True,
        "input_path": input_path,
        "output_path": output_path,
        "original_dimensions": {"width": original_w, "height": original_h},
        "processed_dimensions": {"width": final_w, "height": final_h},
        "steps_applied": steps_applied,
        "processing_time_ms": round(processing_time * 1000, 2),
        "engine": "opencv",
    }


def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "success": False,
            "error": "Usage: python ocr_preprocess.py <input_path> <output_path> [--config <json>]",
        }))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    config = {}
    if "--config" in sys.argv:
        config_idx = sys.argv.index("--config")
        if config_idx + 1 < len(sys.argv):
            try:
                config = json.loads(sys.argv[config_idx + 1])
            except json.JSONDecodeError:
                pass

    result = preprocess_pipeline(input_path, output_path, config)
    print(json.dumps(result))
    sys.exit(0 if result.get("success") else 1)


if __name__ == "__main__":
    main()
