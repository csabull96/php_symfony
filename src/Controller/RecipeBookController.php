<?php


namespace App\Controller;


use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeBookController extends AbstractController
{
    private $path_recipes = "../templates/RecipeBookTemplates/recipes.txt";
    private $path_ingredients = "../templates/RecipeBookTemplates/ingredients.txt";

    /**
     * @param Request $reguest
     * @return Response
     * @Route(name="rbList", path="rb")
     */
    public function rbListAction(Request $reguest) : Response {
        $twig_parameters = ["recipes" => array()];

        if (file_exists($this->path_recipes)) {
            $recipes = file($this->path_recipes, FILE_IGNORE_NEW_LINES);

            foreach ($recipes as $recipe) {
                $name_ingredients_description = explode("|", $recipe);

                $name = $name_ingredients_description[0];
                $ingredients = explode(",", $name_ingredients_description[1]);
                $description = $name_ingredients_description[2];

                foreach ($ingredients as $key => $ingredient) {
                    $ingredient_info = explode("-", $ingredient);
                    $ingredients[$key] = ["ingredient" => $ingredient_info[0], "quantity" => $ingredient_info[1]];
                }

                $entry = ["name" => $name, "ingredients" => $ingredients, "description" => $description];
                $twig_parameters["recipes"] []= $entry;
            }
        }

        return $this->render("RecipeBookTemplates/list.html.twig", $twig_parameters);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="rbAdd", path="rb/add")
     */
    public function rbAddAction(Request $request) : Response {
        $raw_name = $request->request->get("recipe_name");
        $name = $this->sanitizeInput($raw_name);

        if (!$name) {
            $this->addFlash("notice", "Recipe has not been added (missing name).");
            return $this->redirectToRoute("rbList");
        }

        $recipe = "{$name}|";
        $number_of_ingredients = $request->request->get("number_of_ingredients");

        for ($i = 0; $i < $number_of_ingredients; $i++) {
            $ingredient_name = $request->request->get("ingredient_name_{$i}");
            $raw_ingredient_quantity = $request->request->get("ingredient_quantity_{$i}");
            $ingredient_quantity = $this->sanitizeInput($raw_ingredient_quantity);
            $recipe .=  "{$ingredient_name}-{$ingredient_quantity},";
        }

        $recipe = rtrim($recipe, ",");
        $recipe .= "|";

        $raw_description = $request->request->get("description");
        $description = $this->sanitizeInput($raw_description);
        $recipe .= "{$description}\n";

        $file = file_get_contents($this->path_recipes);
        file_put_contents($this->path_recipes,$recipe.$file);
        return $this->redirectToRoute("rbList");
    }

    /**
     * @param Request $reguest
     * @return Response
     * @Route(name="rbForm", path="rb/form")
     */
    public function rbFormAction(Request $reguest) : Response {
        $ingredient_names = file($this->path_ingredients, FILE_IGNORE_NEW_LINES);
        return $this->render("RecipeBookTemplates/form.html.twig", ["current_date" => date("Y.m.d."), "ingredient_names" => $ingredient_names]);
    }

    private function sanitizeInput(string $input) {
        return str_replace(["#", "@", "\r", "\n"], "", $input);
    }

    /**
     * @param Request $request
     * @Route(name="rbListIngredients", path="rb/ingredients")
     */
    public function rbListIngredientsAction(Request $request) : Response {
        $ingredient_names = file($this->path_ingredients, FILE_IGNORE_NEW_LINES);
        return $this->render("RecipeBookTemplates/ingredients.html.twig", ["ingredients" => $ingredient_names]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="rbAddIngredient", path="rb/addingredient")
     */
    public function rbAddIngredientAction(Request $request) : Response {
        $ingredients = file_get_contents($this->path_ingredients);
        $ingredient = $request->request->get("ingredient");

        file_put_contents($this->path_ingredients, $ingredient."\n".$ingredients);

        return $this->redirectToRoute("rbListIngredients");
    }

}